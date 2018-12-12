<?php

namespace Tightenco\NovaGoogleAnalytics;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;
use Spatie\Analytics\Analytics;
use Spatie\Analytics\Period;

class PageViewsMetric extends Value
{
    public $name = 'Page Views';

    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $results = $this->pageViews($request->range);

        return $this
            ->result($results['current'])
            ->previous($results['previous']);
    }

    private function pageViews($range)
    {
        $analyticsData = app(Analytics::class)
            ->fetchTotalVisitorsAndPageViews(
                Period::days(($range * 2) - 1)
            );

        $previous = 0;
        $current = 0;

        for ($i = 0; $i < $range; $i++) {
            $previous += $analyticsData[$i]['pageViews'];
        }

        for ($i = $range; $i < count($analyticsData); $i++) {
            $current += $analyticsData[$i]['pageViews'];
        }

        return [
            'current' => $current,
            'previous' => $previous,
        ];
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            1 => '1 day',
            7 => '7 Days',
            30 => '30 Days',
            60 => '60 Days',
            365 => '365 Days',
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        return now()->addMinutes(30);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'page-views';
    }
}
