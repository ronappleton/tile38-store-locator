<script setup lang="ts">
import { onMounted, ref } from 'vue';
import StoreMap from '@/components/store-locator/StoreMap.vue';

type InitialState = {
    count: number;
    center: { lat: number; lng: number };
    tile38_version: string;
    client_version: string;
};

const props = defineProps<{ initial: InitialState }>();
const benchmark = ref<{
    average_ms: number;
    min_ms: number;
    stores: number;
} | null>(null);

const runBenchmark = async () => {
    const response = await fetch('/api/stores/benchmark');

    if (response.ok) {
        benchmark.value = await response.json();
    }
};

onMounted(() => {
    void runBenchmark();
});
</script>

<template>
    <main class="min-h-screen bg-[#07110f] text-[#d6e0dc]">
        <header
            class="flex items-center justify-between border-b border-[#1d3832] px-5 py-4 lg:px-10"
        >
            <div class="flex items-center gap-3">
                <div
                    class="grid size-9 place-items-center rounded-lg bg-[#2dd4bf] font-mono text-sm font-bold text-[#06201d]"
                >
                    T38
                </div>
                <div>
                    <p
                        class="font-display text-lg font-semibold tracking-tight text-[#eefaf5]"
                    >
                        Store locator
                    </p>
                    <p
                        class="font-mono text-[10px] tracking-[0.2em] text-[#78928a] uppercase"
                    >
                        Tile38 / spatial index lab
                    </p>
                </div>
            </div>
            <div
                class="hidden items-center gap-5 font-mono text-xs text-[#78928a] sm:flex"
            >
                <span>CLIENT {{ props.initial.client_version }}</span>
                <span>TILE38 {{ props.initial.tile38_version }}</span>
                <a
                    class="text-[#2dd4bf] hover:text-[#99f6e4]"
                    href="https://github.com/ronappleton/tile38-php-client"
                    >GitHub ↗</a
                >
            </div>
        </header>

        <section
            class="grid min-h-[calc(100vh-74px)] lg:grid-cols-[minmax(0,1fr)_390px]"
        >
            <div
                class="relative min-h-[62vh] border-b border-[#1d3832] lg:min-h-0 lg:border-r lg:border-b-0"
            >
                <StoreMap
                    :center="props.initial.center"
                    :store-count="props.initial.count"
                />
            </div>

            <aside class="flex flex-col bg-[#0b1916]">
                <div class="border-b border-[#1d3832] p-6">
                    <p
                        class="mb-4 font-mono text-[11px] tracking-[0.25em] text-[#fbbf24] uppercase"
                    >
                        The proof
                    </p>
                    <h1
                        class="font-display text-4xl leading-[0.95] tracking-[-0.04em] text-[#eefaf5]"
                    >
                        One million stores.<br /><span class="text-[#2dd4bf]"
                            >One nearby query.</span
                        >
                    </h1>
                    <p class="mt-5 text-sm leading-6 text-[#9bb0a9]">
                        Move the map or click anywhere. Tile38 searches the full
                        collection and returns only the stores worth rendering.
                    </p>
                </div>

                <div class="grid grid-cols-2 border-b border-[#1d3832]">
                    <div class="border-r border-[#1d3832] p-5">
                        <p
                            class="font-mono text-[10px] tracking-[0.18em] text-[#78928a] uppercase"
                        >
                            Indexed stores
                        </p>
                        <p class="mt-2 font-display text-3xl text-[#eefaf5]">
                            {{ props.initial.count.toLocaleString() }}
                        </p>
                    </div>
                    <div class="p-5">
                        <p
                            class="font-mono text-[10px] tracking-[0.18em] text-[#78928a] uppercase"
                        >
                            Benchmark
                        </p>
                        <p class="mt-2 font-display text-3xl text-[#fbbf24]">
                            {{ benchmark?.average_ms ?? '—'
                            }}<span class="font-mono text-sm"> ms</span>
                        </p>
                    </div>
                </div>

                <div class="border-b border-[#1d3832] p-6">
                    <p
                        class="font-mono text-[10px] tracking-[0.2em] text-[#78928a] uppercase"
                    >
                        PHP client call
                    </p>
                    <pre
                        class="mt-4 overflow-x-auto rounded-lg border border-[#1d3832] bg-[#06100e] p-4 font-mono text-xs leading-6 text-[#b7d1c8]"
                    ><code><span class="text-[#fbbf24]">$client</span>-&gt;nearby(<span class="text-[#2dd4bf]">'stores'</span>, Point::make($lat, $lng))
    -&gt;limit(<span class="text-[#fbbf24]">10</span>)
    -&gt;distance()
    -&gt;points()
    -&gt;execute();</code></pre>
                </div>

                <div class="mt-auto p-6">
                    <button
                        class="w-full rounded-lg bg-[#2dd4bf] px-4 py-3 font-mono text-xs font-semibold tracking-[0.16em] text-[#06201d] uppercase transition hover:bg-[#99f6e4]"
                        type="button"
                        @click="runBenchmark"
                    >
                        Run five-query benchmark
                    </button>
                    <p
                        class="mt-4 font-mono text-[10px] leading-5 text-[#607a72]"
                    >
                        The map never receives 1M markers. It receives the
                        indexed answer: a small, measured result set.
                    </p>
                </div>
            </aside>
        </section>
    </main>
</template>
