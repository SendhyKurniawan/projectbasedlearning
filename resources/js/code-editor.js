// CodeMirror Code Editor Setup
import CodeMirror from 'codemirror';
import 'codemirror/lib/codemirror.css';
import 'codemirror/theme/dracula.css';
import 'codemirror/mode/htmlmixed/htmlmixed.js';
import 'codemirror/mode/css/css.js';
import 'codemirror/mode/javascript/javascript.js';
import 'codemirror/mode/xml/xml.js';

// Initialize code editor
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

    return editor;
};

// Run code in preview iframe
window.runCode = function(editor, previewId) {
    const code = editor.getValue();
    const preview = document.getElementById(previewId);
    
    if (!preview) {
        console.error(`Preview element with id "${previewId}" not found`);
        return;
    }

    const previewDoc = preview.contentDocument || preview.contentWindow.document;
    previewDoc.open();
    previewDoc.write(code);
    previewDoc.close();
};

// Submit code to form
window.submitCode = function(editor, targetInputId) {
    const code = editor.getValue();
    const input = document.getElementById(targetInputId);
    
    if (input) {
        input.value = code;
    }
};

// Export CodeMirror for global access
window.CodeMirror = CodeMirror;
