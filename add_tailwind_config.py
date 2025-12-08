import re
import os

# Tailwind config to inject
tailwind_config = """    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#1e40af',
                        accent: '#f59e0b',
                        electric: '#06b6d4',
                        tech: '#8b5cf6',
                    }
                }
            }
        }
    </script>
"""

# List of HTML files to process
html_files = [
    'about.html', 'products.html', 'product.html', 'cart.html', 
    'checkout.html', 'contact.html', 'account.html',
    'orders.html', 'privacy.html', 'terms.html', '404.html'
]

base_dir = r'c:\Users\Pc\OneDrive - duet.ac.bd\duet\CSE-3-1\web'

for filename in html_files:
    filepath = os.path.join(base_dir, filename)
    
    if not os.path.exists(filepath):
        print(f"Skipping {filename} - file not found")
        continue
    
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Check if tailwind config already exists
    if 'darkMode:' in content:
        print(f"- {filename} already has Tailwind config")
        continue
    
    # Find the Tailwind CDN script tag and add config after it
    pattern = r'(<script src="https://cdn\.tailwindcss\.com"></script>)'
    
    if re.search(pattern, content):
        new_content = re.sub(pattern, r'\1\n' + tailwind_config, content)
        
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"✓ Added Tailwind config to {filename}")
    else:
        print(f"✗ Could not find Tailwind CDN script in {filename}")

print("\nDone!")
