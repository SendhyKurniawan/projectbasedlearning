import CodeMirror from 'codemirror';
import 'codemirror/lib/codemirror.css';
import 'codemirror/theme/dracula.css';
import 'codemirror/theme/material-darker.css';
import 'codemirror/theme/mdn-like.css';
import 'codemirror/mode/htmlmixed/htmlmixed.js';
import 'codemirror/mode/css/css.js';
import 'codemirror/mode/javascript/javascript.js';
import 'codemirror/mode/xml/xml.js';
import 'codemirror/mode/clike/clike.js';
import 'codemirror/mode/php/php.js';

window.serverSideLanguages = ['java', 'php', 'csharp'];

window.cmModeMap = {
    htmlmixed: 'htmlmixed', html: 'htmlmixed', css: 'css', javascript: 'javascript',
    java: 'text/x-java', php: 'application/x-httpd-php', csharp: 'text/x-csharp',
};

window.initCodeEditor = function(elementId, options = {}) {
    const element = document.getElementById(elementId);
    if (!element) {
        console.error(`Element with id "${elementId}" not found`);
        return null;
    }

    const defaultOptions = {
        mode: 'htmlmixed',
        theme: 'dracula',
        lineNumbers: true,
        autoCloseTags: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        indentUnit: 2,
        tabSize: 2,
        indentWithTabs: false,
        lineWrapping: true,
    };

    const editor = CodeMirror.fromTextArea(element, {
        ...defaultOptions,
        ...options
    });

    if (options.fontSize) {
        editor.getWrapperElement().style.fontSize = options.fontSize + 'px';
        editor.refresh();
    }

    window.__codeEditor = editor;
    return editor;
};

// Shim injected into preview iframes that forwards console/runtime errors to the
// parent window via postMessage. Parent listens for { __pblConsole: true }.
const CONSOLE_FORWARD_SHIM = `<script>
(function() {
    function send(level, args) {
        try {
            var message = Array.prototype.map.call(args, function(a) {
                if (a instanceof Error) return a.stack || a.message;
                if (typeof a === 'object') { try { return JSON.stringify(a); } catch (e) { return String(a); } }
                return String(a);
            }).join(' ');
            parent.postMessage({ __pblConsole: true, level: level, message: message }, '*');
        } catch (e) {}
    }
    ['log', 'info', 'warn', 'error'].forEach(function(fn) {
        var orig = console[fn];
        console[fn] = function() { send(fn === 'error' || fn === 'warn' ? 'error' : 'log', arguments); orig.apply(console, arguments); };
    });
    window.addEventListener('error', function(ev) {
        send('error', [ev.message + ' (' + (ev.filename || 'inline') + ':' + ev.lineno + ')']);
    });
    window.addEventListener('unhandledrejection', function(ev) {
        send('error', ['Unhandled promise rejection: ' + (ev.reason && ev.reason.message ? ev.reason.message : ev.reason)]);
    });
})();
<\/script>`;

window.runCode = function(editor, previewId, language) {
    const code = editor.getValue();
    const preview = document.getElementById(previewId);

    if (!preview) {
        console.error(`Preview element with id "${previewId}" not found`);
        return;
    }

    const lang = language || 'htmlmixed';

    if (window.serverSideLanguages?.includes(lang)) {
        return false;
    }

    let html;
    if (lang === 'javascript') {
        html = `<!DOCTYPE html><html><body>${CONSOLE_FORWARD_SHIM}<script>\n${code}\n<\/script></body></html>`;
    } else if (lang === 'css') {
        html = `<!DOCTYPE html><html><head><style>${code}</style></head><body>${CONSOLE_FORWARD_SHIM}<p>CSS Preview — add HTML in your code to see elements.</p></body></html>`;
    } else {
        // htmlmixed/html: inject shim after <body> if present, else prepend.
        if (/<body[^>]*>/i.test(code)) {
            html = code.replace(/<body([^>]*)>/i, '<body$1>' + CONSOLE_FORWARD_SHIM);
        } else {
            html = CONSOLE_FORWARD_SHIM + code;
        }
    }

    const previewDoc = preview.contentDocument || preview.contentWindow.document;
    previewDoc.open();
    previewDoc.write(html);
    previewDoc.close();
};

window.submitCode = function(editor, targetInputId) {
    const code = editor.getValue();
    const input = document.getElementById(targetInputId);

    if (input) {
        input.value = code;
    }
};

window.CodeMirror = CodeMirror;
