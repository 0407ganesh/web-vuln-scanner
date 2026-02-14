import requests
import sys
import json

def scan_website(url):
    results = {
        "url": url,
        "error": None,
        "https": False,
        "redirect_to_https": False,
        "security_headers": {},
        "cookies_secure": [],
        "cookies_httponly": [],
        "vulnerabilities": []  # New: list of issues
    }

    try:
        original_url = url
        if url.startswith("http://"):
            url = url.replace("http://", "https://", 1)

        response = requests.get(
            url,
            allow_redirects=True,
            timeout=10,
            verify=True
        )

        results["https"] = response.url.startswith("https://")

        if original_url.startswith("http://"):
            results["redirect_to_https"] = len(response.history) > 0 and any(
                r.status_code in (301, 302, 307, 308) for r in response.history
            )
        else:
            results["redirect_to_https"] = True

        important_headers = [
            "Strict-Transport-Security",
            "Content-Security-Policy",
            "X-Frame-Options",
            "X-Content-Type-Options",
            "Referrer-Policy",
            "Permissions-Policy"
        ]
        headers_lower = {k.lower(): v for k, v in response.headers.items()}
        for header in important_headers:
            results["security_headers"][header] = header.lower() in headers_lower

        for cookie in response.cookies:
            name = cookie.name
            if cookie.secure:
                results["cookies_secure"].append(name)
            if cookie.has_nonstandard_attr("HttpOnly"):
                results["cookies_httponly"].append(name)

        results["status_code"] = response.status_code

        # === NEW: Classify vulnerabilities ===
        vulns = []

        if not results["https"]:
            vulns.append({
                "type": "Missing HTTPS",
                "severity": "High",
                "description": "Site is not using secure HTTPS protocol. Data can be intercepted."
            })

        if not results["redirect_to_https"]:
            vulns.append({
                "type": "No HTTP to HTTPS Redirect",
                "severity": "Medium",
                "description": "Users accessing via HTTP are not redirected to HTTPS."
            })

        for header, present in results["security_headers"].items():
            if not present:
                severity = "High" if header in ["Strict-Transport-Security", "Content-Security-Policy"] else "Medium"
                vulns.append({
                    "type": f"Missing {header}",
                    "severity": severity,
                    "description": f"The {header} security header is not present."
                })

        # Rough cookie check: if any cookies exist but not all are secure/httponly
        all_cookies = len(response.cookies)
        secure_count = len(results["cookies_secure"])
        httponly_count = len(results["cookies_httponly"])
        if all_cookies > 0 and (secure_count < all_cookies or httponly_count < all_cookies):
            vulns.append({
                "type": "Insecure Cookies",
                "severity": "Medium",
                "description": "Some cookies lack Secure or HttpOnly flags, risking theft or XSS."
            })

        results["vulnerabilities"] = vulns

    except Exception as e:
        results["error"] = str(e)
        if "error" in results and results["error"]:
            results["vulnerabilities"].append({
                "type": "Scan Error",
                "severity": "High",
                "description": f"Failed to scan: {results['error']}"
            })

    return results


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Error: Please provide a URL")
        print("Example: python scan.py https://www.google.com")
        sys.exit(1)

    url = sys.argv[1].strip()
    result = scan_website(url)
    print(json.dumps(result, indent=2))