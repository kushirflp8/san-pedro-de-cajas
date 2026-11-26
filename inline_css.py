"""Copia styles.css (minificado) dentro del <style> de index.html.

Ejecutar después de cada cambio en styles.css:  python inline_css.py
"""
import re
from pathlib import Path

STRING = re.compile(r'''('[^']*'|"[^"]*")''')


def minify(chunk):
    chunk = re.sub(r"\s+", " ", chunk)
    return re.sub(r"\s*([{};])\s*", r"\1", chunk)


root = Path(__file__).parent
css = (root / "styles.css").read_text(encoding="utf-8")
# Minify only outside quoted strings so values like content: "a ; b" stay intact.
css = re.sub(r"/\*.*?\*/", "", css, flags=re.S)
parts = STRING.split(css)
css = "".join(p if i % 2 else minify(p) for i, p in enumerate(parts)).replace(";}", "}")

index = root / "index.html"
html = index.read_text(encoding="utf-8")
pattern = re.compile(r"<style>.*?</style>(?=\s*<link rel=\"prefetch\" href=\"styles\.css)", re.S)
if not pattern.search(html):
    raise SystemExit("No se encontró el <style> de la portada en index.html")
html = pattern.sub(lambda m: "<style>" + css + "</style>", html, count=1)
index.write_text(html, encoding="utf-8", newline="")
print("index.html actualizado")
