import os
import sys
from pathlib import Path

import cv2
import fitz
import numpy as np
import pytesseract

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")


def load_pages(file_path: Path) -> list[np.ndarray]:
    if file_path.suffix.lower() == ".pdf":
        images = []

        with fitz.open(file_path) as document:
            for page in document:
                pixmap = page.get_pixmap(dpi=250, alpha=False)
                image_data = np.frombuffer(pixmap.tobytes("png"), dtype=np.uint8)
                image = cv2.imdecode(image_data, cv2.IMREAD_COLOR)

                if image is None:
                    raise RuntimeError(f"Could not decode PDF page {page.number + 1}.")

                images.append(image)

        return images

    image = cv2.imread(str(file_path), cv2.IMREAD_COLOR)

    if image is None:
        raise RuntimeError("Could not read the uploaded image.")

    return [image]


def extract_text(file_path: Path) -> str:
    tesseract_command = os.environ.get("TESSERACT_CMD")

    if tesseract_command:
        pytesseract.pytesseract.tesseract_cmd = tesseract_command

    extracted_pages = []

    for image in load_pages(file_path):
        grayscale = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        blurred = cv2.GaussianBlur(grayscale, (5, 5), 0)
        _, thresholded = cv2.threshold(
            blurred,
            0,
            255,
            cv2.THRESH_BINARY + cv2.THRESH_OTSU,
        )
        extracted_pages.append(
            pytesseract.image_to_string(
                thresholded,
                lang=os.environ.get("TESSERACT_LANG", "eng"),
                config="--psm 6",
            ).strip()
        )

    return "\n\n".join(page for page in extracted_pages if page)


def main() -> int:
    if len(sys.argv) != 2:
        print("Usage: python ocr_processor.py <file_path>", file=sys.stderr)
        return 2

    file_path = Path(sys.argv[1])

    if not file_path.is_file():
        print(f"File not found: {file_path}", file=sys.stderr)
        return 2

    try:
        text = extract_text(file_path)
    except Exception as error:
        print(f"OCR processing failed: {error}", file=sys.stderr)
        return 1

    print(text)

    return 0


if __name__ == "__main__":
    sys.exit(main())
