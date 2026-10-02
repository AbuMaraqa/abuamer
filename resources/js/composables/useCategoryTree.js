/**
 * Pure helpers for nested category nodes ({ id, children: [...] }) of any depth.
 * Every function is recursive; none assumes a fixed number of levels.
 */

/**
 * Normalise text for matching: case-insensitive, without diacritics (including Hebrew
 * niqqud), and with Arabic letter variants unified (أ إ آ → ا, ى → ي, ة → ه).
 */
export function normalizeSearchText(value) {
    return String(value ?? '')
        .normalize('NFKD')
        .replace(/[\u0300-\u036f\u064b-\u065f\u0670\u0640\u0591-\u05c7]/g, '')
        .replace(/[أإآ]/g, 'ا')
        .replace(/ى/g, 'ي')
        .replace(/ة/g, 'ه')
        .toLowerCase()
        .trim();
}

/**
 * Keep the nodes that match the term plus all of their ancestors, so a result
 * is always shown in its place within the tree.
 *
 * @returns {{ nodes: Array, matchedIds: Set<number>, expandedIds: Set<number> }}
 */
export function filterTree(nodes, term) {
    const needle = normalizeSearchText(term);
    const matchedIds = new Set();
    const expandedIds = new Set();

    const visit = (list) =>
        list.flatMap((node) => {
            const children = visit(node.children ?? []);
            const haystack = [node.name, node.slug, ...Object.values(node.names ?? {})].map(normalizeSearchText);
            const isMatch = haystack.some((text) => text.includes(needle));

            if (isMatch) {
                matchedIds.add(node.id);
            }

            if (children.length > 0) {
                expandedIds.add(node.id);
            }

            return isMatch || children.length > 0 ? [{ ...node, children }] : [];
        });

    return { nodes: visit(nodes), matchedIds, expandedIds };
}

/**
 * The chain of nodes from a root down to the node with the given id (inclusive), or [].
 */
export function findPath(nodes, id) {
    for (const node of nodes) {
        if (node.id === id) {
            return [node];
        }

        const path = findPath(node.children ?? [], id);

        if (path.length > 0) {
            return [node, ...path];
        }
    }

    return [];
}

export function findNode(nodes, id) {
    return findPath(nodes, id).at(-1) ?? null;
}

/**
 * The ids of a node and of every node below it.
 */
export function subtreeIds(node) {
    return [node.id, ...(node.children ?? []).flatMap(subtreeIds)];
}

/**
 * Number of descendant categories and of products in the whole subtree.
 */
export function subtreeStats(node) {
    return (node.children ?? []).reduce(
        (stats, child) => {
            const childStats = subtreeStats(child);

            return {
                categories: stats.categories + 1 + childStats.categories,
                products: stats.products + childStats.products,
            };
        },
        { categories: 0, products: node.products_count ?? 0 },
    );
}

/**
 * Depth-first flat list of { node, depth } for indented option lists.
 */
export function flattenTree(nodes, depth = 0) {
    return nodes.flatMap((node) => [{ node, depth }, ...flattenTree(node.children ?? [], depth + 1)]);
}

/**
 * Turn a name into a URL slug in its own script (Arabic and Hebrew letters are kept).
 * The server normalises slugs the same way; this only previews them while typing.
 *
 * @param {{ script: string }} locale One of the shared `locale.supported` entries.
 */
export function slugify(value, locale) {
    let text = String(value ?? '')
        .trim()
        .replaceAll('×', 'x');

    if (locale.script === 'Latn') {
        text = text.normalize('NFKD').replace(/[\u0300-\u036f]/g, '');
    }

    // Arabic harakat and Hebrew niqqud are not letters, so they are dropped as on the server.
    return text
        .toLowerCase()
        .replace(/[\u064b-\u065f\u0670\u0591-\u05c7]/g, '')
        .replace(/[_\s]+/g, '-')
        .replace(/[^\p{L}\p{N}-]+/gu, '')
        .replace(/-{2,}/g, '-')
        .replace(/^-|-$/g, '');
}
