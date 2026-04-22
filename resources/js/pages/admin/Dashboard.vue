<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { index } from '@/routes/tickets';
import type {
    ChartConfig,
} from "@/components/ui/chart"
import { Donut } from "@unovis/ts"
import { VisAxis, VisGroupedBar, VisXYContainer, VisSingleContainer, VisDonut } from "@unovis/vue"
import { TrendingUp, TrendingDown, Minus } from "lucide-vue-next"
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import {
    ChartContainer,
    ChartCrosshair,
    ChartTooltip,
    ChartTooltipContent,
    componentToString,
} from "@/components/ui/chart"
import { computed } from "vue"

const props = defineProps<{
    chartData: Array<{
        date: string;
        laporan: number;
    }>;
    trendingPercentage: number;
    statusCounts: Record<string, number>;
}>();

const chartConfig = {
    laporan: {
        label: "Laporan",
        color: "var(--chart-1)",
    },
} satisfies ChartConfig

const donatChartData = computed(() => {
    const data = [];
    const counts = props.statusCounts;
    if (counts['open']) data.push({ open: counts['open'], status: "open" });
    if (counts['in_progress']) data.push({ in_progress: counts['in_progress'], status: "in_progress" });
    if (counts['resolved']) data.push({ resolved: counts['resolved'], status: "resolved" });
    if (counts['closed']) data.push({ closed: counts['closed'], status: "closed" });
    if (counts['rejected']) data.push({ rejected: counts['rejected'], status: "rejected" });
    return data;
});

type DataDonut = { status: string;[key: string]: any; }

const donatChartConfig = {
    total: {
        label: "Total Laporan",
        color: undefined,
    },
    open: {
        label: "Open",
        color: "#3b82f6",
    },
    in_progress: {
        label: "In Progress",
        color: "#eab308",
    },
    resolved: {
        label: "Resolved",
        color: "#22c55e",
    },
    closed: {
        label: "Closed",
        color: "#64748b",
    },
    rejected: {
        label: "Rejected",
        color: "#ef4444",
    },
} as const satisfies ChartConfig

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: index(),
            },
        ],
    },
});
</script>

<template>

    <Head title="Admin Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
            <Card class="flex flex-col justify-center pb-0 aspect-video overflow-hidden">
                <CardHeader class="items-center pb-0">
                    <CardTitle>Status Laporan</CardTitle>
                </CardHeader>
                <CardContent class="flex-1 pb-0">
                    <ChartContainer :config="donatChartConfig" class="mx-auto aspect-square max-h-[230px]">
                        <VisSingleContainer :data="donatChartData" :margin="{ top: 30, bottom: 30 }">
                            <VisDonut :value="(d: DataDonut) => d[d.status]"
                                :color="(d: DataDonut) => donatChartConfig[d.status as keyof typeof donatChartConfig].color"
                                :arc-width="0" />
                            <ChartTooltip :triggers="{
                                [Donut.selectors.segment]: componentToString(donatChartConfig, ChartTooltipContent, { hideLabel: true })!,
                            }" />
                        </VisSingleContainer>
                    </ChartContainer>
                </CardContent>
            </Card>
        </div>
        <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <Card>
                <CardHeader>
                    <CardTitle>Chart Laporan {{ new Date().getFullYear() }}</CardTitle>
                    <CardDescription>Januari - Desember {{ new Date().getFullYear() }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <ChartContainer :config="chartConfig" class="h-[350px] w-full">
                        <VisXYContainer
                            :data="chartData.map(item => ({ date: new Date(item.date), laporan: item.laporan }))"
                            :margin="{ left: -24 }" :y-domain="[0, undefined]">
                            <VisGroupedBar :x="(d: any) => d.date" :y="(d: any) => d.laporan"
                                :color="chartConfig.laporan.color" :rounded-corners="10" />
                            <VisAxis type="x" :x="(d: any) => d.date" :tick-line="false" :domain-line="false"
                                :grid-line="false" :num-ticks="6" :tick-format="(d: number) => {
                                    const date = new Date(d)
                                    return date.toLocaleDateString('id-ID', {
                                        month: 'short',
                                    })
                                }" :tick-values="chartData.map(d => new Date(d.date).getTime())" />
                            <VisAxis type="y" :num-ticks="3" :tick-line="false" :domain-line="false" />
                            <ChartTooltip />
                            <ChartCrosshair
                                :template="componentToString(chartConfig, ChartTooltipContent, { hideLabel: true })"
                                color="#0000" />
                        </VisXYContainer>
                    </ChartContainer>
                </CardContent>
                <CardFooter class="flex-col items-start gap-2 text-sm">
                    <div class="flex gap-2 font-medium leading-none"
                        :class="{ 'text-emerald-500': trendingPercentage > 0, 'text-red-500': trendingPercentage < 0, 'text-gray-500': trendingPercentage === 0 }">
                        {{ trendingPercentage > 0 ? 'Naik' : (trendingPercentage
                            < 0 ? 'Turun' : 'Stabil') }} {{ Math.abs(trendingPercentage) }}% bulan ini <TrendingUp
                            v-if="trendingPercentage > 0" class="h-4 w-4" />
                        <TrendingDown v-else-if="trendingPercentage < 0" class="h-4 w-4" />
                        <Minus v-else class="h-4 w-4" />
                    </div>
                    <div class="leading-none text-muted-foreground">
                        Menampilkan total laporan tiap bulan.
                    </div>
                </CardFooter>
            </Card>

        </div>
    </div>
</template>
