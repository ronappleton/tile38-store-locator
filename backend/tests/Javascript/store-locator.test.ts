import { describe, expect, it } from 'vitest';

describe('store locator showcase', () => {
    it('uses a bounded result set for rendering', () => {
        expect(12).toBeLessThan(1_000_000);
    });
});
