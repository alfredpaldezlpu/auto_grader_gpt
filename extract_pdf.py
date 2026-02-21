"""
Extract text from a PDF file and output it to stdout.
Usage: python extract_pdf.py <path_to_pdf>
"""
import sys
import os
import fitz  # PyMuPDF

# Force UTF-8 output on Windows
if sys.platform == 'win32':
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')

def extract_text(pdf_path):
    try:
        doc = fitz.open(pdf_path)
        text = ""
        for page in doc:
            text += page.get_text()
        doc.close()
        # Clean up problematic unicode characters
        text = text.encode('utf-8', errors='replace').decode('utf-8')
        return text.strip()
    except Exception as e:
        print(f"ERROR: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python extract_pdf.py <pdf_path>", file=sys.stderr)
        sys.exit(1)
    print(extract_text(sys.argv[1]))
