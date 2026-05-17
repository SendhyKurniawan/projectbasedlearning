// Lightweight markdown renderer for read-only views (no EasyMDE editor).
// Used on mahasiswa/materials/show and anywhere markdown is displayed, not edited.
import { marked } from "marked";
import hljs from "highlight.js";
import "highlight.js/styles/github.css";

window.renderMarkdown = function (markdownText) {
    if (!markdownText) return "";

    marked.setOptions({
        highlight: function (code, lang) {
            if (lang && hljs.getLanguage(lang)) {
                try {
                    return hljs.highlight(code, { language: lang }).value;
                } catch (e) {
                    console.error("Highlight error:", e);
                }
            }
            return hljs.highlightAuto(code).value;
        },
        breaks: true,
        gfm: true,
        headerIds: true,
        mangle: false,
    });

    return marked.parse(markdownText);
};

window.autoRenderMarkdown = function () {
    document.querySelectorAll("[data-markdown]").forEach(function (element) {
        const markdown = element.getAttribute("data-markdown");
        if (markdown) {
            element.innerHTML = window.renderMarkdown(markdown);
        }
    });
};

window.marked = marked;
window.hljs = hljs;
