import requests
import sys
import json
from urllib.parse import urlparse

def scan_website(url):
    results = {
        "url": url,
        "error": None,
        "https": False,
        "redirect_to_https": False,
        "security_headers": {},
        "cookies_secure": [],
        "cookies_httponly": [],
        "vulnerabilities": [],
        "status_code": None
    }

    try:
        # Normalize URL
        original_url = url
        if not url.startswith(('http://', 'https://')):
            url = 'https://' + url

        # Make request with redirect handling
        response = requests.get(
            url,
            allow_redirects=True,
            timeout=12,
            verify=True,
            headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) WebVulnScanner/1.0'}
        )

        results["status_code"] = response.status_code
        results["https"] = response.url.startswith("https://")

        # Check HTTP → HTTPS redirect
        if original_url.startswith("http://"):
            results["redirect_to_https"] = len(response.history) > 0 and any(
                r.status_code in (301, 302, 307, 308) and r.headers.get('Location', '').startswith('https://')
                for r in response.history
            )
        else:
            results["redirect_to_https"] = True

        # Security headers check
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

        # Cookies check
        for cookie in response.cookies:
            name = cookie.name
            if cookie.secure:
                results["cookies_secure"].append(name)
            if cookie.has_nonstandard_attr("HttpOnly"):
                results["cookies_httponly"].append(name)

        # Vulnerability classification
        vulns = []

        if not results["https"]:
            vulns.append({
                "type": "Missing HTTPS",
                "severity": "High",
                "description": "Site is not using secure HTTPS protocol. Data can be intercepted."
            })

        if not results["redirect_to_https"]:
            vulns.append({
                "type": "No HTTP → HTTPS Redirect",
                "severity": "Medium",
                "description": "Users accessing via HTTP are not automatically redirected to HTTPS."
            })

        for header, present in results["security_headers"].items():
            if not present:
                severity = "High" if header in ["Strict-Transport-Security", "Content-Security-Policy"] else "Medium"
                vulns.append({
                    "type": f"Missing {header}",
                    "severity": severity,
                    "description": f"The {header} security header is not present."
                })

        # Insecure cookies check
        total_cookies = len(response.cookies)
        secure_count = len(results["cookies_secure"])
        httponly_count = len(results["cookies_httponly"])
        if total_cookies > 0 and (secure_count < total_cookies or httponly_count < total_cookies):
            vulns.append({
                "type": "Insecure Cookies",
                "severity": "Medium",
                "description": "Some cookies lack Secure or HttpOnly flags, risking theft or XSS."
            })

        results["vulnerabilities"] = vulns

    except requests.exceptions.SSLError as ssl_err:
        results["error"] = f"SSL/TLS error: {str(ssl_err)}"
        results["vulnerabilities"].append({
            "type": "SSL/TLS Issue",
            "severity": "High",
            "description": f"SSL/TLS connection failed: {str(ssl_err)}"
        })
    except requests.exceptions.RequestException as req_err:
        results["error"] = str(req_err)
        results["vulnerabilities"].append({
            "type": "Connection Error",
            "severity": "High",
            "description": f"Failed to connect: {str(req_err)}"
        })
    except Exception as e:
        results["error"] = str(e)
        results["vulnerabilities"].append({
            "type": "Scan Error",
            "severity": "High",
            "description": f"Unexpected error: {str(e)}"
        })

    return results


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No URL provided. Usage: python scan.py https://example.com"}, indent=2))
        sys.exit(1)

    url = sys.argv[1].strip()
    result = scan_website(url)
    print(json.dumps(result, indent=2))