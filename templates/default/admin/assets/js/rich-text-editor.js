(function() {
    'use strict';

    class RichTextEditor {
        constructor(wrapperId) {
            const wrapper = document.getElementById(wrapperId);
            if (!wrapper) {
                return;
            }

            this.wrapper = wrapper;
            this.editor = wrapper.querySelector('.rich-text-editor');
            this.hiddenTextarea = wrapper.querySelector('textarea[name="content[content]"]');
            this.toolbar = wrapper.querySelector('.rich-text-toolbar');

            if (!this.editor) {
                return;
            }
            if (!this.hiddenTextarea) {
                return;
            }

            this.init();
        }

        init() {
            this.handleToolbarClick = this.handleToolbarClick.bind(this);
            this.syncContent = this.syncContent.bind(this);
            this.updateToolbarState = this.updateToolbarState.bind(this);
            
            if (!this.editor || !this.editor.isContentEditable) {
                return;
            }
            
            this.editor.addEventListener('input', this.syncContent);
            this.editor.addEventListener('keyup', this.syncContent);
            this.editor.addEventListener('keyup', this.updateToolbarState);
            this.editor.addEventListener('mouseup', this.updateToolbarState);
            this.editor.addEventListener('paste', (e) => {
                e.preventDefault();
                const text = e.clipboardData.getData('text/plain');
                document.execCommand('insertText', false, text);
                this.syncContent();
            });
            
            if (this.toolbar) {
                this.toolbar.replaceWith(this.toolbar.cloneNode(true));
                this.toolbar = this.wrapper.querySelector('.rich-text-toolbar');
                this.toolbar.addEventListener('click', this.handleToolbarClick);
            }
            
            this.syncContent();
        }

        handleToolbarClick(e) {
            e.preventDefault();
            e.stopPropagation();

            const btn = e.target.closest('button[data-command]');
            if (!btn) return;

            const command = btn.dataset.command;

            this.editor.focus();

            setTimeout(() => {
                if (command === 'createLink') {
                    const selection = window.getSelection();
                    const selectedText = selection.toString();
                    
                    if (selectedText && this.isTextInLink()) {
                        document.execCommand('unlink', false, null);
                    } else if (selectedText) {
                        const url = prompt(lang === 'ru' ? 'Введите URL ссылки:' : 'Enter link URL:', 'https://');
                        if (url) {
                            document.execCommand('createLink', false, url);
                        }
                    } else {
                        alert(lang === 'ru' ? 'Выделите текст для создания ссылки' : 'Select text to create a link');
                    }
                } else if (command === 'unlink') {
                    document.execCommand('unlink', false, null);
                } else if (command === 'formatCode') {
                    this.toggleCodeFormat();
                } else if (command === 'removeFormat') {
                    document.execCommand('removeFormat', false, null);
                } else {
                    document.execCommand(command, false, null);
                }
                this.syncContent();
                this.updateToolbarState();
            }, 10);
        }

        toggleCodeFormat() {
            const selection = window.getSelection();
            if (!selection.rangeCount) return;

            const selectedText = selection.toString();
            if (!selectedText) {
                alert(lang === 'ru' ? 'Выделите текст для оформления как код' : 'Select text to format as code');
                return;
            }

            if (this.isTextInCode()) {
                document.execCommand('removeFormat', false, null);
            } else {

                const range = selection.getRangeAt(0);
                const codeElement = document.createElement('code');
                codeElement.textContent = selectedText;
                range.deleteContents();
                range.insertNode(codeElement);
                range.setStartAfter(codeElement);
                range.setEndAfter(codeElement);
                selection.removeAllRanges();
                selection.addRange(range);
            }
        }

        isTextInCode() {
            const selection = window.getSelection();
            if (!selection.rangeCount) return false;
            
            const range = selection.getRangeAt(0);
            const node = range.commonAncestorContainer;
            
            let current = node.nodeType === 3 ? node.parentNode : node;
            while (current && current !== this.editor) {
                if (current.tagName === 'CODE') {
                    return true;
                }
                current = current.parentNode;
            }
            return false;
        }

        isTextInLink() {
            const selection = window.getSelection();
            if (!selection.rangeCount) return false;
            
            const range = selection.getRangeAt(0);
            const node = range.commonAncestorContainer;
            
            let current = node.nodeType === 3 ? node.parentNode : node;
            while (current && current !== this.editor) {
                if (current.tagName === 'A') {
                    return true;
                }
                current = current.parentNode;
            }
            return false;
        }

        updateToolbarState() {
            if (!this.toolbar) return;

            const isBold = document.queryCommandState('bold');
            const isItalic = document.queryCommandState('italic');
            const isUnderline = document.queryCommandState('underline');
            const isStrikethrough = document.queryCommandState('strikeThrough');
            const isInLink = this.isTextInLink();
            const isInCode = this.isTextInCode();

            this.toolbar.querySelector('[data-command="bold"]')?.classList.toggle('active', isBold);
            this.toolbar.querySelector('[data-command="italic"]')?.classList.toggle('active', isItalic);
            this.toolbar.querySelector('[data-command="underline"]')?.classList.toggle('active', isUnderline);
            this.toolbar.querySelector('[data-command="strikeThrough"]')?.classList.toggle('active', isStrikethrough);
            this.toolbar.querySelector('[data-command="createLink"]')?.classList.toggle('active', isInLink);
            this.toolbar.querySelector('[data-command="formatCode"]')?.classList.toggle('active', isInCode);
        }

        syncContent() {
            if (this.editor && this.hiddenTextarea) {
                this.hiddenTextarea.value = this.editor.innerHTML;
            }
        }
    }

    window.RichTextEditor = RichTextEditor;

    function initAllEditors() {
        const wrappers = document.querySelectorAll('.rich-text-wrapper');
        wrappers.forEach(wrapper => {
            const id = wrapper.id;
            if (id && !wrapper.dataset.editorInitialized) {
                new RichTextEditor(id);
                wrapper.dataset.editorInitialized = 'true';
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllEditors);
    } else {
        initAllEditors();
    }

    document.addEventListener('shown.bs.modal', initAllEditors);
    
})();