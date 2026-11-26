"""Copia styles.css (minificado) dentro del <style> de index.html.

Ejecutar después de cada cambio en styles.css:  python inline_css.py
"""
import re
from pathlib import Path

root = Path(__file__).parent
css = (root / "styles.css").read_text(encoding="utf-8")
css = re.sub(r"/\*.*?\*/", "", css, flags=re.S)
css = re.sub(r"\s+", " ", css)
css = re.sub(r"\s*([{};])\s*", r"\1", css).replace(";}", "}")

index = root / "index.html"
html = index.read_text(encoding="utf-8")
pattern = re.compile(r"<style>.*?</style>(?=\s*<link rel=\"prefetch\" href=\"styles\.css)", re.S)
if not pattern.search(html):
    raise SystemExit("No se encontró el <style> de la portada en index.html")
html = pattern.sub(lambda m: "<style>" + css + "</style>", html, count=1)
index.write_text(html, encoding="utf-8", newline="")
print("index.html actualizado")
