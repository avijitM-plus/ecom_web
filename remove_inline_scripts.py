import re
import os

# List of HTML files to process (all except index.html)
html_files = [
    'about.html', 'products.html', 'product.html', 'cart.html', 
    'checkout.html', 'contact.html', 'account.html', 'login.html',
    'signup.html', 'orders.html', 'privacy.html', 'terms.html', '404.html'
]

base_dir = r'c:\Users\Pc\OneDrive - duet.ac.bd\duet\CSE-3-1\web'

for filename in html_files:
    filepath = os.path.join(base_dir, filename)
    
    if not os.path.exists(filepath):
        print(f"Skipping {filename} - file not found")
        continue
    
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Pattern to match inline script blocks that contain dark mode code
    # Look for <script> tags that contain darkModeToggle
    pattern = r'<script>\s*\(function\(\)\{[^<]*darkModeToggle[^<]*\}\)\(\);\s*</script>\s*'
    
    # Remove the inline script
    new_content = re.sub(pattern, '', content, flags=re.DOTALL)
    
    # Check if anything was removed
    if new_content != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"✓ Removed inline script from {filename}")
    else:
        print(f"- No inline script found in {filename}")

print("\nDone!")
