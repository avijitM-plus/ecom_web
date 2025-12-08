import re
import os

# Dark mode toggle CSS to add
toggle_css = """        /* Dark Mode Toggle Styles */
        .dark-mode-toggle { 
            position: relative; 
            display: inline-block; 
            width: 60px; 
            height: 30px; 
        }
        .dark-mode-toggle input { 
            opacity: 0; 
            width: 0; 
            height: 0; 
        }
        .slider { 
            position: absolute; 
            cursor: pointer; 
            top: 0; 
            left: 0; 
            right: 0; 
            bottom: 0; 
            background-color: #ccc; 
            transition: 0.4s; 
            border-radius: 30px; 
        }
        .slider:before { 
            position: absolute; 
            content: ""; 
            height: 22px; 
            width: 22px; 
            left: 4px; 
            bottom: 4px; 
            background-color: white; 
            transition: 0.4s; 
            border-radius: 50%; 
        }
        input:checked + .slider { 
            background-color: #06b6d4; 
        }
        input:checked + .slider:before { 
            transform: translateX(30px); 
        }
"""

files_to_update = ['login.html', 'signup.html']
base_dir = r'c:\Users\Pc\OneDrive - duet.ac.bd\duet\CSE-3-1\web'

for filename in files_to_update:
    filepath = os.path.join(base_dir, filename)
    
    if not os.path.exists(filepath):
        print(f"Skipping {filename} - file not found")
        continue
    
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Check if toggle CSS already exists
    if '.dark-mode-toggle' in content:
        print(f"- {filename} already has toggle CSS")
        continue
    
    # Find the closing </style> tag and add CSS before it
    pattern = r'(\s+)(</style>)'
    
    if re.search(pattern, content):
        new_content = re.sub(pattern, r'\n' + toggle_css + r'\1\2', content)
        
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"✓ Added toggle CSS to {filename}")
    else:
        print(f"✗ Could not find </style> tag in {filename}")

print("\nDone!")
