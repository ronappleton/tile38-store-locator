import { describe, expect, it } from 'vitest';
import { highlightPhp, highlightShell } from '@/lib/php';

describe('highlightPhp', () => {
    it('escapes HTML before highlighting', () => {
        const html = highlightPhp('$x = "<script>";');
        expect(html).not.toContain('<script>');
        expect(html).toContain('&lt;script&gt;');
    });

    it('wraps keywords in a teal span', () => {
        const html = highlightPhp('return $result->execute();');
        expect(html).toContain('text-teal-300');
    });

    it('highlights variables, strings and method calls distinctly', () => {
        const html = highlightPhp("$client->set('stores', $id, Point::make(1, 2))->execute();");

        expect(html).toContain('text-cyan-300'); // $variables
        expect(html).toContain('text-amber-200'); // strings
        expect(html).toContain('text-slate-500'); // -> operators
    });

    it('returns unchanged plain text when there is nothing to tokenize', () => {
        expect(highlightPhp('hello')).toContain('hello');
    });
});

describe('highlightShell', () => {
    it('highlights composer commands', () => {
        const html = highlightShell('composer require ronappleton/tile38-php-client');
        expect(html).toContain('text-teal-300');
    });

    it('escapes markup', () => {
        expect(highlightShell('echo <b>')).not.toContain('<b>');
    });
});
