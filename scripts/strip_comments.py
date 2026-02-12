
import os
import sys

ROOT = os.path.abspath(os.getcwd())
EXTS = {
    '.php', '.js', '.css', '.html', '.htm', '.sql', '.py', '.sh', '.c', '.cpp', '.h', '.java', '.rb', '.lua', '.ts', '.jsx', '.tsx'
}


def should_process(path):
    _, ext = os.path.splitext(path.lower())
    return ext in EXTS


def strip_comments(content, path_ext):
    i = 0
    n = len(content)
    out = []
    in_single = False
    in_double = False
    in_back = False
    escaped = False
    in_sline_comment = False
    in_mline_comment = False
    in_html_comment = False
    while i < n:
        ch = content[i]
        nxt = content[i+1] if i+1 < n else ''

        if in_sline_comment:
            if ch == '\n':
                in_sline_comment = False
                out.append(ch)
            
            i += 1
            continue

        if in_mline_comment:
            if ch == '*' and nxt == '/':
                in_mline_comment = False
                i += 2
            else:
                i += 1
            continue

        if in_html_comment:
            if ch == '-' and content[i:i+3] == '-->':
                in_html_comment = False
                i += 3
            else:
                i += 1
            continue

        if not (in_single or in_double or in_back):
            
            if ch == '/' and nxt == '*':
                in_mline_comment = True
                i += 2
                continue
            if ch == '/' and nxt == '/':
                in_sline_comment = True
                i += 2
                continue
            if ch == '#' and (path_ext in ('.sh', '.py') or True):
                
                in_sline_comment = True
                i += 1
                continue
            if ch == '<' and content[i:i+4] == '<!--':
                in_html_comment = True
                i += 4
                continue
            if path_ext == '.sql' and ch == '-' and nxt == '-':
                in_sline_comment = True
                i += 2
                continue

        
        if not in_single and not in_double and not in_back:
            if ch == '"':
                in_double = True
                out.append(ch)
                i += 1
                continue
            if ch == "'":
                in_single = True
                out.append(ch)
                i += 1
                continue
            if ch == '`':
                in_back = True
                out.append(ch)
                i += 1
                continue

        else:
            
            out.append(ch)
            if ch == '\\' and not escaped:
                escaped = True
                i += 1
                continue
            if escaped:
                escaped = False
                i += 1
                continue
            if in_double and ch == '"':
                in_double = False
                i += 1
                continue
            if in_single and ch == "'":
                in_single = False
                i += 1
                continue
            if in_back and ch == '`':
                in_back = False
                i += 1
                continue
            i += 1
            continue

        
        out.append(ch)
        i += 1

    return ''.join(out)


def process_file(path):
    try:
        with open(path, 'r', encoding='utf-8', errors='replace') as f:
            content = f.read()
    except Exception:
        return False

    _, ext = os.path.splitext(path.lower())
    new = strip_comments(content, ext)
    if new != content:
        try:
            with open(path, 'w', encoding='utf-8') as f:
                f.write(new)
            print(f"Stripped comments: {path}")
            return True
        except Exception as e:
            print(f"Failed write {path}: {e}")
            return False
    return False


def main():
    changed = 0
    files = 0
    for root, dirs, filenames in os.walk(ROOT):
        for name in filenames:
            path = os.path.join(root, name)
            rel = os.path.relpath(path, ROOT)
            if should_process(path):
                files += 1
                if process_file(path):
                    changed += 1

    print(f"Processed {files} files, changed {changed} files.")


if __name__ == '__main__':
    main()
