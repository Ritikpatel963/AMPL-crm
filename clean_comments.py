import os
import re
import glob

def clean_comments(directory):
    pattern_full_line = re.compile(r'^[ \t]*//\s*ad\s*(start|close)[^\n]*\n?', re.MULTILINE | re.IGNORECASE)
    pattern_inline = re.compile(r'[ \t]*//\s*ad\s*(start|close)[^\n]*', re.IGNORECASE)
    
    java_files = glob.glob(os.path.join(directory, '**/*.java'), recursive=True)
    
    for filepath in java_files:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
            
        original_content = content
        
        # Remove full line comments first
        content = pattern_full_line.sub('', content)
        
        # Remove inline comments next
        content = pattern_inline.sub('', content)
        
        if content != original_content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Cleaned {filepath}")

if __name__ == '__main__':
    clean_comments('E:/website-project/Amplchat/APP/app/src/main/java/com/ampl_chat')
    print("Done")
