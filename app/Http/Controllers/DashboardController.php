<?php

namespace App\Http\Controllers;
use App\Http\Helpers\ApiResponse;
use App\Models\Content;
use App\Models\Subscriber;
use Carbon\Carbon;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class DashboardController extends Controller
{
    protected $bigQuery = null;
    protected $bigQueryKey = null;

    public function __construct(){

        $this->bigQueryKey = config('services.bigqueryAPI');
        $this->bigQuery = new BigQueryClient([
            'keyFile' => json_decode($this->bigQueryKey, true)
        ]);

    }


    public function filter_array($array,$term){
        $matches = array();
        foreach($array as $a){
            if(intval($a->id) == intval($term))
                $matches[] = $a;
        }
        return $matches;
    }


    public function getTotalUsers(Request $request)
    {
        try {
            $today = date('Y-m-d');
            $selectedDate = $request->input('date', $today);
            $date = Carbon::parse($selectedDate)->format('Y-m-d');

            $cacheKey = 'totalUsers_' . $date;
            $isCached = Cache::has($cacheKey);

            $totalUsers = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($date) {
                return Subscriber::whereDate('created_at', '<=', $date)->count();
            });

            return ApiResponse::success(['value' => $totalUsers, 'cached' => $isCached], null, $isCached ? 'Total Users fetched from cache.' : 'Total Users fetched successfully.');

        } catch (\Throwable $e) {
            return ApiResponse::error(500, $e->getMessage(), 'Something went wrong!');
        }
    }

    public function getTotalVodViews(Request $request)
    {
        try {
            $today = date('Y-m-d');
            $selectedDate = $request->input('date', $today);
            $date = Carbon::parse($selectedDate)->format('Y-m-d');

            $cacheKey = 'totalVodViews_' . $date;
            $isCached = Cache::has($cacheKey);

            $totalViews = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($date) {
                $query = "SELECT id, partitioned_at Date FROM `t-sports-361206.events.current_viewers` WHERE
                partitioned_at = '" . Carbon::parse($date)->format('Y-m-d') . "' AND content_type='VOD'";

                $jobConfig = $this->bigQuery->query($query);
                $totalViewsQuery = $this->bigQuery->runQuery($jobConfig);

                return $totalViewsQuery->info()['totalRows'];
            });

            return ApiResponse::success(['value' => $totalViews, 'cached' => $isCached], null, $isCached ? 'Total VOD views fetched from cache.' : 'Total VOD views fetched successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(500, $e->getMessage(), 'Something went wrong!');
        }
    }


    public function getLiveAudience(Request $request) {
        try {
            $today = date('Y-m-d');
            $selectedDate = $request->input('date', $today);

            $start = strtotime(Carbon::parse($selectedDate)->format('Y-m-d H:i:s'));
            $end = strtotime(Carbon::parse($selectedDate)->format('Y-m-d H:i:s'));
            $mins = ($end - $start) / 60;

            $minutes = $mins ?? 10;
            $query = "SELECT COUNT(DISTINCT subscriber_id) AS total_live_audience
            FROM (
                (SELECT subscriber_id, reporting_time, partitioned_at FROM `t-sports-361206.events.current_viewers`
                    WHERE partitioned_at = DATE('" . Carbon::parse($selectedDate)->format('Y-m-d') . "') AND reporting_time BETWEEN DATETIME_SUB(DATETIME_TRUNC(DATETIME(CURRENT_TIMESTAMP(), 'Asia/Dhaka'), MINUTE), INTERVAL 10 MINUTE) AND DATETIME_SUB(DATETIME_TRUNC(DATETIME(CURRENT_TIMESTAMP(), 'Asia/Dhaka'), SECOND), INTERVAL 0 SECOND)
                ) UNION ALL (SELECT subscriber_id, reporting_time, partitioned_at FROM `t-sports-361206.events.heart_beat`
                    WHERE partitioned_at = DATE('" . Carbon::parse($selectedDate)->format('Y-m-d') . "') AND reporting_time BETWEEN DATETIME_SUB(DATETIME_TRUNC(DATETIME(CURRENT_TIMESTAMP(), 'Asia/Dhaka'), MINUTE), INTERVAL 10 MINUTE) AND DATETIME_SUB(DATETIME_TRUNC(DATETIME(CURRENT_TIMESTAMP(), 'Asia/Dhaka'), SECOND), INTERVAL 0 SECOND)
                )
            ) AS live_audience_count;
        ";

            $jobConfig = $this->bigQuery->query($query);
            $totalAudienceQuery = $this->bigQuery->runQuery($jobConfig);
            $totalAudience = $totalAudienceQuery->info()['rows'][0]['f'][0]['v'];

            return ApiResponse::success(['value' => $totalAudience], null, 'Total Live Audience fetched successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }



    public function getTotalLiveViews(Request $request)
    {
        try {
            $today = date('Y-m-d');
            $selectedDate = $request->input('date', $today);
            $date = Carbon::parse($selectedDate)->format('Y-m-d');

            $cacheKey = 'totalLiveViews_' . $date;
            $isCached = Cache::has($cacheKey);

            $totalViews = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($date) {
                $query = "SELECT id, partitioned_at Date FROM `t-sports-361206.events.current_viewers` WHERE
                    partitioned_at = '" . Carbon::parse($date)->format('Y-m-d') . "' AND content_type='LIVE'";

                $jobConfig = $this->bigQuery->query($query);
                $totalViewsQuery = $this->bigQuery->runQuery($jobConfig);

                return $totalViewsQuery->info()['totalRows'];
            });

            return ApiResponse::success(['value' => $totalViews, 'cached' => $isCached], null, $isCached ? 'Total Live views fetched from cache.' : 'Total Live views fetched successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(500, 'Internal Server Error', $e->getMessage());
        }
    }

    public function getTodayLoginUsers(Request $request)
    {
        $today = date('Y-m-d');
        $selectedDate = $request->input('date', $today);
        $date = Carbon::parse($selectedDate)->format('Y-m-d');

        $cacheKey = 'todayLoginUsers_' . $date;
        $isCached = Cache::has($cacheKey);

        $loginUsers = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($date) {
            $query = "SELECT id FROM `t-sports-361206.events.login_log` WHERE EXTRACT(DATE FROM reporting_time) = '" . Carbon::parse($date)->format('Y-m-d') . "'";
            $jobConfig = $this->bigQuery->query($query);
            $results = $this->bigQuery->runQuery($jobConfig);

            return $results->info()['totalRows'];
        });

        return ApiResponse::success(['value' => $loginUsers, 'cached' => $isCached], null, $isCached ? 'Login users fetched from cache.' : 'Login users fetched successfully for the specified date.');
    }






    public function getTopTenContent(Request $request)
    {
        $today = date('Y-m-d');
        $selectedDate = $request->input('date', $today);

        // Build the query
        $query = "SELECT CONTENT_ID, content_type, COUNT(DISTINCT subscriber_id) AS USERS, ROUND(SUM(TOTAL_TIME_SPENT)/60/60,2) AS TIME_SPENT_HOURLY, SUM(TOTAL_VIEWS) AS VIEWS FROM (
            SELECT CONTENT_ID, subscriber_id, content_type, TOTAL_TIME_SPENT, TOTAL_VIEWS, DD, EE FROM (
                SELECT CONTENT_ID, subscriber_id, content_type, 0 AS TOTAL_TIME_SPENT, 1 TOTAL_VIEWS, 0 AS DD, 1 AS EE FROM `t-sports-361206.events.current_viewers`
                WHERE partitioned_at = DATE('" . Carbon::parse($selectedDate)->format('Y-m-d') . "') AND content_id != 0
                UNION ALL
                SELECT CONTENT_ID, subscriber_id, content_type, 30 AS TOTAL_TIME_SPENT, 0 TOTAL_VIEWS, 1 AS DD, 0 AS EE FROM `t-sports-361206.events.heart_beat`
                WHERE partitioned_at = DATE('" . Carbon::parse($selectedDate)->format('Y-m-d') . "') AND content_id != 0
            )
        ) GROUP BY CONTENT_ID, content_type ORDER BY USERS DESC LIMIT 10";

        // Generate a unique cache key based on the query
        $cacheKey = 'top_ten_content_' . md5($query);
        $isCached = Cache::has($cacheKey);

        // Check if the result is already cached
        if (Cache::has($cacheKey)) {
            $topTenContent = Cache::get($cacheKey);
        } else {
            // If not cached, execute the query and store the result in the cache
            $jobConfig = $this->bigQuery->query($query);
            $toptenConQuery = $this->bigQuery->startQuery($jobConfig);
            $toptencon = $toptenConQuery->queryResults();

            $toptendata = Content::select('id', 'feature_banner as thumb', 'content_name as title')->get();
            $i = 0;
            $topTenContent = [];

            foreach ($toptencon as $row) {
                foreach ($row as $column => $value) {
                    $topTenContent[$i][$column] = json_encode($value);
                    if ($column == 'CONTENT_ID') {
                        $new_array = $this->filter_array($toptendata, json_encode($value));
                        if (count($new_array) > 0) {
                            $topTenContent[$i]['thumb'] = $new_array[0]->thumb;
                            $topTenContent[$i]['title'] = $new_array[0]->title;
                            $topTenContent[$i]['id'] = $new_array[0]->id;
                        } else {
                            $topTenContent[$i]['id'] = json_encode($value);
                        }
                    }
                }
                ++$i;
            }

            // Store the result in the cache for future use (adjust expiration time as needed)
            Cache::put($cacheKey, $topTenContent, now()->addHours(1));
        }

        return ApiResponse::success(['value' => $topTenContent,'cached' => $isCached], null, 'Top ten live content fetched successfully.');
    }

    public function getTopVodContent(Request $request)
    {
        $today = date('Y-m-d');
        $selectedDate = $request->input('date', $today);
        $date = Carbon::parse($selectedDate)->format('Y-m-d');

        $cacheKey = 'topVodContent_' . $date;
        $isCached = Cache::has($cacheKey);

        $topVodContent = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($date) {
            $query = "
            SELECT
                CONTENT_ID,
                content_type,
                COUNT(DISTINCT subscriber_id) AS USERS,
                ROUND(SUM(TOTAL_TIME_SPENT) / 60 / 60, 2) AS TIME_SPENT_HOURLY,
                SUM(TOTAL_VIEWS) AS VIEWS
            FROM (
                SELECT
                    CONTENT_ID,
                    subscriber_id,
                    content_type,
                    TOTAL_TIME_SPENT,
                    TOTAL_VIEWS,
                    DD,
                    EE
                FROM (
                    SELECT
                        CONTENT_ID,
                        subscriber_id,
                        content_type,
                        0 AS TOTAL_TIME_SPENT,
                        1 AS TOTAL_VIEWS,
                        0 AS DD,
                        1 AS EE
                    FROM `t-sports-361206.events.current_viewers`
                    WHERE partitioned_at = DATE('" . Carbon::parse($date)->format('Y-m-d') . "')
                        AND content_id != 0
                    UNION ALL
                    SELECT
                        CONTENT_ID,
                        subscriber_id,
                        content_type,
                        30 AS TOTAL_TIME_SPENT,
                        0 AS TOTAL_VIEWS,
                        1 AS DD,
                        0 AS EE
                    FROM `t-sports-361206.events.heart_beat`
                    WHERE partitioned_at = DATE('" . Carbon::parse($date)->format('Y-m-d') . "')
                        AND content_id != 0
                )
            )
            WHERE content_type = 'VOD'
            GROUP BY CONTENT_ID, content_type
            ORDER BY USERS DESC
            LIMIT 1";

            $jobConfig = $this->bigQuery->query($query);
            $topVodContentQuery = $this->bigQuery->startQuery($jobConfig);
            $topVodContentResults = $topVodContentQuery->queryResults();

            $topVoddata = Content::select('id', 'feature_banner as thumb', 'content_name as title')->where(['content_type' => 'VOD'])->get();
            $i = 0;
            $result = [];
            foreach ($topVodContentResults as $row) {
                foreach ($row as $column => $value) {
                    $result[$i][$column] = json_encode($value);
                    if ($column == 'CONTENT_ID') {
                        $new_array = $this->filter_array($topVoddata, json_encode($value));
                        if (count($new_array) > 0) {
                            $result[$i]['thumb'] = $new_array[0]->thumb;
                            $result[$i]['title'] = $new_array[0]->title;
                            $result[$i]['id'] = $new_array[0]->id;
                        } else {
                            $result[$i]['id'] = json_encode($value);
                        }
                    }
                }
                ++$i;
            }
            return $result;
        });

        return ApiResponse::success(['value' => array_shift($topVodContent,), 'cached' => $isCached], null, $isCached ? 'Top VOD content fetched from cache.' : 'Top VOD content fetched successfully.');
    }

//
    public function getTopLiveContent(Request $request)
    {
        $today = date('Y-m-d');
        $selectedDate = $request->input('date', $today);
        $date = Carbon::parse($selectedDate)->format('Y-m-d');

        $cacheKey = 'topLiveContent_' . $date;
        $isCached = Cache::has($cacheKey);

        $topLiveContent = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($date) {
            $query = "
            SELECT
                CONTENT_ID,
                content_type,
                COUNT(DISTINCT subscriber_id) AS USERS,
                ROUND(SUM(TOTAL_TIME_SPENT) / 60 / 60, 2) AS TIME_SPENT_HOURLY,
                SUM(TOTAL_VIEWS) AS VIEWS
            FROM (
                SELECT
                    CONTENT_ID,
                    subscriber_id,
                    content_type,
                    TOTAL_TIME_SPENT,
                    TOTAL_VIEWS,
                    DD,
                    EE
                FROM (
                    SELECT
                        CONTENT_ID,
                        subscriber_id,
                        content_type,
                        0 AS TOTAL_TIME_SPENT,
                        1 AS TOTAL_VIEWS,
                        0 AS DD,
                        1 AS EE
                    FROM `t-sports-361206.events.current_viewers`
                    WHERE partitioned_at = DATE('" . Carbon::parse($date)->format('Y-m-d') . "')
                        AND content_id != 0
                    UNION ALL
                    SELECT
                        CONTENT_ID,
                        subscriber_id,
                        content_type,
                        30 AS TOTAL_TIME_SPENT,
                        0 AS TOTAL_VIEWS,
                        1 AS DD,
                        0 AS EE
                    FROM `t-sports-361206.events.heart_beat`
                    WHERE partitioned_at = DATE('" . Carbon::parse($date)->format('Y-m-d') . "')
                        AND content_id != 0
                )
            )
            WHERE content_type = 'LIVE'
            GROUP BY CONTENT_ID, content_type
            ORDER BY USERS DESC
            LIMIT 1";

            $tjobConfig = $this->bigQuery->query($query);
            $topLiveContentQuery = $this->bigQuery->startQuery($tjobConfig);
            $topLiveContentResults = $topLiveContentQuery->queryResults();

            $topLivedata = Content::select('id', 'feature_banner as thumb', 'content_name as title')->where(['content_type' => 'LIVE'])->get();
            $i = 0;
            $result = [];
            foreach ($topLiveContentResults as $row) {
                foreach ($row as $column => $value) {
                    $result[$i][$column] = json_encode($value);
                    if ($column == 'CONTENT_ID') {
                        $new_array = $this->filter_array($topLivedata, json_encode($value));
                        if (count($new_array) > 0) {
                            $result[$i]['thumb'] = $new_array[0]->thumb;
                            $result[$i]['title'] = $new_array[0]->title;
                            $result[$i]['id'] = $new_array[0]->id;
                        } else {
                            $result[$i]['id'] = json_encode($value);
                        }
                    }
                }
                ++$i;
            }
            return $result;
        });

        return ApiResponse::success(['value' => array_shift($topLiveContent), 'cached' => $isCached], null, $isCached ? 'Top LIVE content fetched from cache.' : 'Top LIVE content fetched successfully.');
    }

    public function getMonthlyActiveUsers(Request $request)
    {
        $today = date('Y-m-d');
        $selectedDate = $request->input('date', $today);
        $date = Carbon::parse($selectedDate)->format('Y-m-d');

        $cacheKey = 'monthlyActiveUsers_' . $date;
        $isCached = Cache::has($cacheKey);

        $monthlyActiveUsersReportData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($date) {
            $query = "
            SELECT
                Year_Month,
                COUNT(DISTINCT User) User
            FROM (
                SELECT
                    CAST(FORMAT_DATE('%Y-%m', Date) AS STRING) Year_Month,
                    CAST(userId AS STRING) User
                FROM (
                    SELECT
                        subscriber_id AS userId,
                        partitioned_at Date
                    FROM
                        `t-sports-361206.events.current_viewers`
                    WHERE
                        partitioned_at BETWEEN DATE_ADD('" . Carbon::parse($date)->format('Y-m-d') . "', INTERVAL -30 DAY) AND DATE_ADD('" . $date . "', INTERVAL 1 DAY)
                    UNION ALL
                    SELECT
                        subscriber_id AS userId,
                        partitioned_at date
                    FROM
                        `t-sports-361206.events.heart_beat`
                    WHERE
                        partitioned_at BETWEEN DATE_ADD('" . Carbon::parse($date)->format('Y-m-d') . "', INTERVAL -30 DAY) AND DATE_ADD('" . $date . "', INTERVAL 1 DAY)
                    UNION ALL
                    SELECT
                        subscriber_id AS userId,
                        partitioned_at date
                    FROM
                        `t-sports-361206.events.login_log`
                    WHERE
                        partitioned_at BETWEEN DATE_ADD('" . Carbon::parse($date)->format('Y-m-d') . "', INTERVAL -30 DAY) AND DATE_ADD('" . $date . "', INTERVAL 1 DAY)
                )
            )
            GROUP BY
                Year_Month
            ORDER BY
                Year_Month ASC";

            $jobConfig = $this->bigQuery->query($query);
            $queryResults = $this->bigQuery->startQuery($jobConfig)->queryResults();

            $monthlyActiveUsersReportData = [];
            $j = 0;
            foreach ($queryResults as $row) {
                foreach ($row as $column => $value) {
                    if ($column == 'USAGE_DATE') {
                        $monthlyActiveUsersReportData[$j][$column] = $value->get()->format('m/d/Y');
                    } else {
                        $monthlyActiveUsersReportData[$j][$column] = $value;
                    }
                }
                ++$j;
            }

            return $monthlyActiveUsersReportData;
        });

        return ApiResponse::success(['value' => array_pop($monthlyActiveUsersReportData), 'cached' => $isCached], null, $isCached ? 'Monthly active users fetched from cache.' : 'Monthly active users fetched successfully.');
    }

    public function getDailyActiveUsers(Request $request)
    {
        $today = date('Y-m-d');
        $selectedDate = $request->input('date', $today);
        $date = Carbon::parse($selectedDate)->format('Y-m-d');

        $cacheKey = 'dailyActiveUsers_' . $date;
        $isCached = Cache::has($cacheKey);

        $dailyActiveUsersReportData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($date) {
            $query = "
            SELECT
                Date,
                COUNT(DISTINCT User) User
            FROM (
                SELECT
                    Date,
                    CAST(userId AS STRING) User
                FROM (
                    SELECT
                        subscriber_id AS userId,
                        partitioned_at Date
                    FROM
                        `t-sports-361206.events.current_viewers`
                    WHERE
                        DATE(partitioned_at) = DATE('" . $date . "')
                    UNION ALL
                    SELECT
                        subscriber_id AS userId,
                        partitioned_at date
                    FROM
                        `t-sports-361206.events.heart_beat`
                    WHERE
                        DATE(partitioned_at) = DATE('" . $date . "')
                    UNION ALL
                    SELECT
                        subscriber_id AS userId,
                        partitioned_at date
                    FROM
                        `t-sports-361206.events.login_log`
                    WHERE
                        DATE(partitioned_at) = DATE('" . $date . "')
                )
            )
            GROUP BY
                Date
            ORDER BY
                Date ASC";

            $jobConfig = $this->bigQuery->query($query);
            $queryResults = $this->bigQuery->startQuery($jobConfig)->queryResults();

            $dailyActiveUsersReportData = [];
            $j = 0;
            foreach ($queryResults as $row) {
                foreach ($row as $column => $value) {
                    if ($column == 'USAGE_DATE') {
                        $dailyActiveUsersReportData[$j][$column] = $value->get()->format('m/d/Y');
                    } else {
                        $dailyActiveUsersReportData[$j][$column] = $value;
                    }
                }
                ++$j;
            }

            return $dailyActiveUsersReportData;
        });

        return ApiResponse::success(['value' => array_shift($dailyActiveUsersReportData), 'cached' => $isCached], null, $isCached ? 'Daily active users fetched from cache.' : 'Daily active users fetched successfully.');
    }

    public function getHeightConcurrencyUsers(Request $request)
    {
        $today = date('Y-m-d');
        $selectedDate = $request->input('date', $today);
        $date = Carbon::parse($selectedDate)->format('Y-m-d');

        $cacheKey = 'heightConcurrencyUsers_' . $date;
        $isCached = Cache::has($cacheKey);

        $heightConcurrencyReportData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($date) {
            $concurrencyQuery = "SELECT
            USAGE_DATE,
            USAGE_HOUR,
            COUNT(DISTINCT CUSTOMER_ID) AS TOTAL_USER
          FROM (
            SELECT
              subscriber_id CUSTOMER_ID,
              DATE(reporting_time) AS USAGE_DATE,
              CAST(FORMAT_DATETIME('%H', reporting_time) AS STRING) AS USAGE_HOUR,
              CONTENT_TYPE
            FROM (
              SELECT
                subscriber_id,
                content_id,
                content_type,
                device_type,
                reporting_time
              FROM
                `t-sports-361206.events.heart_beat`
              WHERE
                DATE(partitioned_at) = DATE('" . $date . "')
              UNION ALL
              SELECT
                subscriber_id,
                content_id,
                content_type,
                device_type,
                reporting_time
              FROM
                `t-sports-361206.events.current_viewers`
              WHERE
                DATE(partitioned_at) = DATE('" . $date . "') )
            WHERE
              CONTENT_TYPE != 'NULL' )
          GROUP BY
            USAGE_DATE,
            USAGE_HOUR
          ORDER BY
            TOTAL_USER DESC
          LIMIT 1";

            $concurrencyJobConfig = $this->bigQuery->query($concurrencyQuery);
            $concurrencyConQuery = $this->bigQuery->startQuery($concurrencyJobConfig);
            $concurrencycon = $concurrencyConQuery->queryResults();
            $concurrencyReportData = [];
            $j = 0;
            foreach ($concurrencycon as $row) {
                foreach ($row as $column => $value) {
                    if ($column == 'USAGE_DATE') {
                        $concurrencyReportData[$j][$column] = $value->get()->format('m/d/Y');
                    } else {
                        $concurrencyReportData[$j][$column] = $value;
                    }
                }
                ++$j;
            }

            return $concurrencyReportData;
        });

        return ApiResponse::success(['value' => array_shift($heightConcurrencyReportData), 'cached' => $isCached], null, $isCached ? 'Highest concurrency fetched from cache.' : 'Highest concurrency fetched successfully.');
    }

    public function getHourlyGraphReport(Request $request)
    {
        $selectedDate = $request->input('date', now()->format('Y-m-d'));
        $startDate = Carbon::parse($selectedDate)->subDays(2)->startOfDay();
        $endDate = Carbon::parse($selectedDate)->endOfDay();

        $cacheKey = 'hourlyGraphReport_' . $selectedDate;
        $isCached = Cache::has($cacheKey);

        $hourlyReportData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($startDate, $endDate) {
            $hourlyQuery = "
            SELECT
                DATE(reporting_time) AS USAGE_DATE,
                SUBSTR(FORMAT_DATETIME('%A', reporting_time), 1, 3) AS DAY_NAME,
                CAST(FORMAT_DATETIME('%H', reporting_time) AS STRING) AS USAGE_HOUR,
                COUNT(DISTINCT subscriber_id) AS TOTAL_USER
            FROM (
                SELECT subscriber_id, content_id, content_type, device_type, reporting_time
                FROM `t-sports-361206.events.heart_beat`
                WHERE TIMESTAMP(reporting_time) BETWEEN TIMESTAMP('$startDate') AND TIMESTAMP('$endDate')
                UNION ALL
                SELECT subscriber_id, content_id, content_type, device_type, reporting_time
                FROM `t-sports-361206.events.current_viewers`
                WHERE TIMESTAMP(reporting_time) BETWEEN TIMESTAMP('$startDate') AND TIMESTAMP('$endDate')
            )
            GROUP BY USAGE_DATE, DAY_NAME, USAGE_HOUR
            ORDER BY USAGE_DATE, USAGE_HOUR ASC";

            $hourlyJobConfig = $this->bigQuery->query($hourlyQuery);
            $hourlyConQuery = $this->bigQuery->startQuery($hourlyJobConfig);
            $hourlycon = $hourlyConQuery->queryResults();

            $hourlyReportData = [];

            foreach ($hourlycon as $row) {
                $date = $row['USAGE_DATE']->get();
                $dayName = $row['DAY_NAME'];
                $hour = $row['USAGE_HOUR'];
                $totalUser = $row['TOTAL_USER'];

                // Check if the dayName entry already exists in the array
                $existingDayIndex = array_search($dayName, array_column($hourlyReportData, 'name'));

                if ($existingDayIndex !== false) {
                    // Day entry exists, update the data array
                    $hourlyReportData[$existingDayIndex]['data'][] = [
                        'hour' => $hour,
                        'totalUser' => $totalUser,
                    ];
                } else {
                    // Day entry does not exist, create a new entry
                    $hourlyReportData[] = [
                        'name' => $dayName,
                        'categories' => [],
                        'data' => [
                            [
                                'hour' => $hour,
                                'totalUser' => $totalUser,
                            ],
                        ],
                    ];
                }

                // Check if the hour is already in the categories
                $existingHourIndex = array_search($hour, $hourlyReportData[$existingDayIndex]['categories']);

                if ($existingHourIndex === false) {
                    // Hour does not exist in categories, add it
                    $hourlyReportData[$existingDayIndex]['categories'][] = $hour;
                }
            }

            return $hourlyReportData;
        });

        return ApiResponse::success(['value' => $hourlyReportData, 'cached' => $isCached], null, $isCached ? 'Hourly Report fetched from cache.' : 'Hourly Report fetched successfully.');
    }



}
