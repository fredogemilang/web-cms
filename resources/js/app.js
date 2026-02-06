import './bootstrap';

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import TextAlign from '@tiptap/extension-text-align';
import OfficePaste from '@intevation/tiptap-extension-office-paste';

// Store editor instances outside Alpine's reactive scope
window._tiptapEditors = window._tiptapEditors || {};
import Sortable from 'sortablejs';
window.Sortable = Sortable;

document.addEventListener('alpine:init', () => {
    Alpine.data('tiptapEditor', (modelName = 'content') => {
        // Use closure to keep editor reference non-reactive
        let editorInstance = null;
        let editorId = null;
        
        return {
            // Return editor via function to bypass Alpine proxy
            getEditor() {
                return editorInstance;
            },
            
            init() {
                editorId = this.$el.id || 'tiptap-' + Date.now();
                
                // Check if already initialized
                if (window._tiptapEditors[editorId]) {
                    editorInstance = window._tiptapEditors[editorId];
                    return;
                }

                // Get initial content from Livewire
                const initialContent = this.$wire.get(modelName) || '';

                editorInstance = new Editor({
                    element: this.$refs.editor,
                    extensions: [
                        StarterKit.configure({
                            heading: { levels: [1, 2, 3] },
                            link: {
                                openOnClick: false,
                                HTMLAttributes: { class: 'text-blue-500 hover:underline' },
                            },
                        }),
                        Image.configure({
                            HTMLAttributes: { class: 'rounded-lg max-w-full h-auto' },
                        }),
                        Placeholder.configure({
                            placeholder: 'Start writing your story...',
                            emptyEditorClass: 'is-editor-empty',
                        }),
                        TextAlign.configure({
                            types: ['heading', 'paragraph'],
                        }),
                        OfficePaste,
                    ],
                    editorProps: {
                        attributes: {
                            class: 'prose prose-sm dark:prose-invert max-w-none focus:outline-none min-h-[500px] p-6',
                        },
                        clipboardTextParser: (text, context) => {
                            // Check if text contains HTML tags
                            const hasHtmlTags = /<[a-z][\s\S]*>/i.test(text);
                            
                            if (hasHtmlTags) {
                                // Parse HTML string into a temporary DOM element
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(text, 'text/html');
                                
                                // Return the parsed HTML body content
                                // TipTap will convert this to proper nodes
                                return doc.body;
                            }
                            
                            // Return null to use default text parsing
                            return null;
                        },
                    },
                    content: initialContent,
                    onBlur: () => {
                        // Sync to Livewire on blur
                        this.$wire.set(modelName, editorInstance.getHTML());
                    },
                });

                window._tiptapEditors[editorId] = editorInstance;
                
                // Listen for media picker selection
                Livewire.on('tiptap-media-selected', (data) => {
                    // Only process if this is the active editor
                    if (editorInstance && window.activeTiptapEditorId === editorId) {
                        editorInstance.chain().focus().setImage({ 
                            src: data.url, 
                            alt: data.alt || '' 
                        }).run();
                    }
                });
            },

            // Toolbar commands - access editor via closure, not Alpine
            toggleBold() {
                if (editorInstance) editorInstance.chain().focus().toggleBold().run();
            },
            toggleItalic() {
                if (editorInstance) editorInstance.chain().focus().toggleItalic().run();
            },
            toggleStrike() {
                if (editorInstance) editorInstance.chain().focus().toggleStrike().run();
            },
            toggleHeading(level) {
                if (editorInstance) editorInstance.chain().focus().toggleHeading({ level }).run();
            },
            toggleBulletList() {
                if (editorInstance) editorInstance.chain().focus().toggleBulletList().run();
            },
            toggleOrderedList() {
                if (editorInstance) editorInstance.chain().focus().toggleOrderedList().run();
            },
            toggleBlockquote() {
                if (editorInstance) editorInstance.chain().focus().toggleBlockquote().run();
            },
            toggleCodeBlock() {
                if (editorInstance) editorInstance.chain().focus().toggleCodeBlock().run();
            },
            setHorizontalRule() {
                if (editorInstance) editorInstance.chain().focus().setHorizontalRule().run();
            },
            setLink() {
                const previousUrl = editorInstance.getAttributes('link').href;
                const url = window.prompt('URL', previousUrl);
                
                // cancelled
                if (url === null) {
                    return;
                }
                
                // empty
                if (url === '') {
                    editorInstance.chain().focus().extendMarkRange('link').unsetLink().run();
                    return;
                }
                
                // update
                editorInstance.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
            },
            unsetLink() {
                if (editorInstance) editorInstance.chain().focus().unsetLink().run();
            },
            addImage() {
                // Fallback to URL prompt if Media Picker is not available
                const url = window.prompt('Image URL');
                if (url) {
                    editorInstance.chain().focus().setImage({ src: url }).run();
                }
            },
            openMediaPicker() {
                window.activeTiptapEditorId = editorId;
                Livewire.dispatch('openTiptapMediaPicker');
            },
            // Insert image from Media Library (called via Livewire event)
            insertMediaImage(url, alt) {
                if (editorInstance && url) {
                    editorInstance.chain().focus().setImage({ src: url, alt: alt || '' }).run();
                }
            },
            setTextAlign(align) {
                if (editorInstance) editorInstance.chain().focus().setTextAlign(align).run();
            },
            undo() {
                if (editorInstance) editorInstance.chain().focus().undo().run();
            },
            redo() {
                if (editorInstance) editorInstance.chain().focus().redo().run();
            },
            clearFormatting() {
                if (editorInstance) editorInstance.chain().focus().unsetAllMarks().clearNodes().run();
            },
            isActive(name, attrs = {}) {
                return editorInstance ? editorInstance.isActive(name, attrs) : false;
            },

            syncContent() {
                if (editorInstance) {
                    this.$wire.set(modelName, editorInstance.getHTML());
                }
            },

            destroy() {
                if (editorInstance) {
                    editorInstance.destroy();
                    delete window._tiptapEditors[editorId];
                    editorInstance = null;
                }
            },
        };
    });
});

