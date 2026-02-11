// Markdown Editor Setup for Materials
import EasyMDE from "easymde";
import "easymde/dist/easymde.min.css";
import { marked } from "marked";
import hljs from "highlight.js";
import "highlight.js/styles/github.css";

// Initialize Markdown Editor for Dosen
window.initMarkdownEditor = function (elementId, options = {}) {
    const element = document.getElementById(elementId);
    if (!element) {
        console.error(`Element with id "${elementId}" not found`);
        return null;
    }

    const defaultOptions = {
        element: element,
        spellChecker: false,
        placeholder:
            "Tulis materi dengan Markdown...\n\nContoh code block:\n```html\n<h1>Hello World</h1>\n```\n\nContoh bold: **text tebal**\nContoh italic: *text miring*",
        toolbar: [
            "bold",
            "italic",
            "heading",
            "|",
            "code",
            "quote",
            "unordered-list",
            "ordered-list",
            "|",
            "link",
            "image",
            "|",
            "preview",
            "side-by-side",
            "fullscreen",
            "|",
            "guide",
        ],
        minHeight: "400px",
        renderingConfig: {
            codeSyntaxHighlighting: true,
        },
        previewRender: function (plainText) {
            return renderMarkdown(plainText);
        },
    };

    const editor = new EasyMDE({
        ...defaultOptions,
        ...options,
    });

    return editor;
};

// Render Markdown for viewing (Mahasiswa & Preview)
window.renderMarkdown = function (markdownText) {
    if (!markdownText) return "";

    // Configure marked with highlight.js
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

// Auto-render all markdown content on page
window.autoRenderMarkdown = function () {
    document.querySelectorAll("[data-markdown]").forEach(function (element) {
        const markdown = element.getAttribute("data-markdown");
        if (markdown) {
            element.innerHTML = renderMarkdown(markdown);
        }
    });
};

// Export for global use
window.marked = marked;
window.hljs = hljs;
