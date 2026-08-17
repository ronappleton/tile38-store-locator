/**
 * Minimal, dependency-free PHP syntax highlighter for the code rail.
 * Produces safe HTML spans. Good enough for showcasing short client snippets.
 */

const KEYWORDS = new Set([
    'use',
    'class',
    'function',
    'return',
    'new',
    'foreach',
    'as',
    'if',
    'else',
    'public',
    'private',
    'protected',
    'static',
    'final',
    'readonly',
    'enum',
    'case',
    'match',
    'fn',
    'namespace',
    'extends',
    'implements',
    'const',
    'default',
    'continue',
    'break',
    'try',
    'catch',
    'throw',
    'true',
    'false',
    'null',
    'declare',
    'strict_types',
    'require',
    'require_once',
    'echo',
]);

const TOKEN_RE =
    /(\/\/[^\n]*|\/\*[\s\S]*?\*\/|#[^\n]*)|('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")|(\$\w+)|\b(\d+(?:\.\d+)?)\b|([A-Za-z_]\w*)|(->|=>|::|\.{3}|\.\.|\.|[()\[\]{},;:+\-*/%=<>!?&|])/g;

function escapeHtml(value: string): string {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function span(cls: string, content: string): string {
    return `<span class="${cls}">${content}</span>`;
}

export function highlightPhp(source: string): string {
    let output = '';
    let lastIndex = 0;

    const tokens = source.matchAll(TOKEN_RE);

    for (const match of tokens) {
        const index = match.index ?? 0;
        const [full, comment, string, variable, number, identifier, operator] =
            match;

        if (index > lastIndex) {
            output += escapeHtml(source.slice(lastIndex, index));
        }

        lastIndex = index + full.length;

        if (comment) {
            output += span('text-slate-500 italic', escapeHtml(comment));
        } else if (string) {
            output += span('text-amber-200', escapeHtml(string));
        } else if (variable) {
            output += span('text-cyan-300', escapeHtml(variable));
        } else if (number) {
            output += span('text-amber-300', escapeHtml(number));
        } else if (identifier) {
            if (KEYWORDS.has(identifier)) {
                output += span('text-teal-300', identifier);
            } else if (/^[A-Z]/.test(identifier)) {
                output += span('text-teal-200', identifier);
            } else {
                output += span('text-slate-300', identifier);
            }
        } else if (operator) {
            if (operator === '->' || operator === '::') {
                output += span('text-slate-500', operator);
            } else {
                output += span('text-slate-400', operator);
            }
        }
    }

    if (lastIndex < source.length) {
        output += escapeHtml(source.slice(lastIndex));
    }

    return output;
}

export function highlightShell(source: string): string {
    let output = '';
    let lastIndex = 0;

    const tokens = source.matchAll(
        /(#.*)|(\$[\w-]+)|(\b(?:composer|php|make|docker)\b)|(`[^`]*`)|(\[[^\]]+\])/g,
    );

    for (const match of tokens) {
        const index = match.index ?? 0;
        const [full, comment, variable, command, string] = match;

        if (index > lastIndex) {
            output += escapeHtml(source.slice(lastIndex, index));
        }

        lastIndex = index + full.length;

        if (comment) {
            output += span('text-slate-500 italic', escapeHtml(comment));
        } else if (variable) {
            output += span('text-amber-200', escapeHtml(variable));
        } else if (command) {
            output += span('text-teal-300', command);
        } else if (string) {
            output += span('text-amber-200', escapeHtml(string));
        } else {
            output += span('text-slate-300', full);
        }
    }

    if (lastIndex < source.length) {
        output += escapeHtml(source.slice(lastIndex));
    }

    return output;
}
