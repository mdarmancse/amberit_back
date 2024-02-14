<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Models\Content;
use App\Models\Subscriber;
use App\Models\Subscription;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

class ReportController extends Controller
{
    protected $bigQuery = null;
    protected $bigQueryKey = null;

    public function __construct(){

        $this->bigQueryKey = config('services.bigqueryAPI');
        $this->bigQuery = new BigQueryClient([
            'keyFile' => json_decode($this->bigQueryKey, true)
        ]);

    }
    function filter_array($array,$term){
        $matches = array();
        foreach($array as $a){
            if(intval($a->id) == intval($term))
                $matches[] = $a;
        }
        return $matches;
    }

    public function contentViewsDateWise(Request $request)
    {
        try {
            $reportContent = [];
            $today=date('Y-m-d');
            $start_date = $request->start_date ?? $today;
            $end_date = $request->end_date ?? $today;
            $type = $request->type ?? 1;

            $content_query='';
            if ($type == 2){
                $content_query='AND content_type="LIVE"';
            }elseif ($type == 3){
                $content_query='AND content_type="VOD"';
            }else{
                $content_query='';
            }

                $query = "SELECT
                CONTENT_ID,
                content_type,
                COUNT(DISTINCT subscriber_id) AS USERS,
                ROUND(SUM(TOTAL_TIME_SPENT)/60/60,2) AS TIME_SPENT_HOURLY,
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
                        1 TOTAL_VIEWS,
                        0 AS DD,
                        1 AS EE
                        FROM
                        `t-sports-361206.events.current_viewers`
                        WHERE
                        FORMAT_DATE('%Y-%m-%d',partitioned_at) BETWEEN '" . Carbon::parse($start_date)->format('Y-m-d') . "'
                            AND '" . Carbon::parse($end_date)->format('Y-m-d') . "'
                        AND content_id !=0  $content_query
                        UNION ALL
                        SELECT
                        CONTENT_ID,
                        subscriber_id,
                        content_type,
                        30 AS TOTAL_TIME_SPENT,
                        0 TOTAL_VIEWS,
                        1 AS DD,
                        0 AS EE
                        FROM
                        `t-sports-361206.events.heart_beat`
                        WHERE
                        FORMAT_DATE('%Y-%m-%d',partitioned_at) BETWEEN '" . Carbon::parse($start_date)->format('Y-m-d') . "'
                            AND '" . Carbon::parse($end_date)->format('Y-m-d') . "'
                        AND content_id !=0  $content_query ) )
                    GROUP BY
                    CONTENT_ID,
                    content_type
                    ORDER BY
                    USERS DESC";

            //return $query;

                $jobConfig = $this->bigQuery->query($query);
                $Results = $this->bigQuery->startQuery($jobConfig);
                $contents = $Results->queryResults();

                $toptendata = Content::select('id', 'feature_banner as thumb', 'content_name as title')->get();

                $i = 1;

                foreach ($contents as $row) {
                    foreach ($row as $column => $value) {
                        $reportContent[$i][$column] = json_encode($value);
                        if ($column == 'CONTENT_ID') {
                            $new_array = $this->filter_array($toptendata, json_encode($value));
                            if (count($new_array) > 0) {
                                $reportContent[$i]['thumb'] = $new_array[0]->thumb;
                                $reportContent[$i]['title'] = $new_array[0]->title;
                                $reportContent[$i]['id'] = $new_array[0]->id;
                            } else {
                                $reportContent[$i]['id'] = json_encode($value);
                            }
                            $reportContent[$i]['sl'] = $i;

                        }
                    }
                    ++$i;
                }

                // Paginate the data
                $perPage = $request->get('pageSize', 10);
                $page = $request->get('pageIndex', 1);
                $total = count($reportContent);

                $paginator = new LengthAwarePaginator(
                    array_slice($reportContent, ($page - 1) * $perPage, $perPage),
                    $total,
                    $perPage,
                    $page
                );

                $totalCount = $total;


                return ApiResponse::success($paginator, $totalCount, 'Resource fetched successfully.');


        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
            }
    }


    public function getLoginLogViewReport(Request $request)
    {
        try {

            $reportContent = [];
            $today = date('Y-m-d');
            $start_date = $request->start_date ?? $today;
            $end_date = $request->end_date ?? $today;

            $query = ($request->type == 1) ? $this->getLoginLogDailyQuery($start_date, $end_date) : $this->getLoginLogHourlyQuery($start_date, $end_date);

            $jobConfig = $this->bigQuery->query($query);
            $Results = $this->bigQuery->startQuery($jobConfig);
            $contents = $Results->queryResults();

            $i = 1;

            foreach ($contents as $row) {
                foreach ($row as $column => $value) {
                    $reportContent[$i]['id'] = $i + 1;
                    if ($column == 'USAGE_DATE') {
                        $reportContent[$i][$column] = Carbon::parse($value)->format('m/d/Y');
                    } else {
                        $reportContent[$i][$column] = $value;
                    }
                    $reportContent[$i]['sl'] = $i;

                }
                ++$i;
            }

            // Paginate the data
            $perPage = $request->get('pageSize', 10);
            $page = $request->get('pageIndex', 1);
            $total = count($reportContent);

            $paginator = new LengthAwarePaginator(
                array_slice($reportContent, ($page - 1) * $perPage, $perPage),
                $total,
                $perPage,
                $page
            );

            $totalCount = $total;

            if ($request->type == 1 ) {
                return ApiResponse::success($paginator, $totalCount, 'Daily Login Log Report fetched successfully.');
            } else {
                return ApiResponse::success($paginator, $totalCount, 'Hourly Login Log Report fetched successfully.');
            }

        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function getconCurrentUserReport(Request $request)
    {
        try {
            $reportContent = [];
            $today = date('Y-m-d');
            $start_date = $request->start_date ?? $today;
            $end_date = $request->end_date ?? $today;


            $query = ($request->type == 2) ? $this->getConCurrentUserHourlyQuery($start_date, $end_date) : $this->getConCurrentUserDailyQuery($start_date, $end_date);

            $jobConfig = $this->bigQuery->query($query);
            $Results = $this->bigQuery->startQuery($jobConfig);
            $contents = $Results->queryResults();

            $i = 1;

            foreach ($contents as $row) {
                foreach ($row as $column => $value) {
                    $reportContent[$i]['id'] = $i + 1;
                    if ($column == 'USAGE_DATE') {
                        $reportContent[$i][$column] = Carbon::parse($value)->format('m/d/Y');
                    } else {
                        $reportContent[$i][$column] = $value;
                    }
                    $reportContent[$i]['sl'] = $i;

                }
                ++$i;
            }

            // Paginate the data
            $perPage = $request->get('pageSize', 10);
            $page = $request->get('pageIndex', 1);
            $total = count($reportContent);

            $paginator = new LengthAwarePaginator(
                array_slice($reportContent, ($page - 1) * $perPage, $perPage),
                $total,
                $perPage,
                $page
            );

            $totalCount = $total;


            if ($request->hourly_daily == 1) {
                return ApiResponse::success($paginator, $totalCount, 'Daily Concurrent Users Report fetched successfully.');
            } else {
                return ApiResponse::success($paginator, $totalCount, 'Hourly Concurrent Users Report fetched successfully.');
            }

        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function getUniqueUserMonthlyReport(Request $request)
    {
        try {
            $reportContent = [];
            $today = date('Y-m-d');
            $start_date = $request->start_date ?? $today;
            $end_date = $request->end_date ?? $today;

            if ($start_date || $end_date) {
                $query = "SELECT
                    Year_Month,
                    COUNT( DISTINCT User) User,
                    FROM (
                    SELECT
                    CAST(FORMAT_DATE('%Y-%m', Date) AS STRING) Year_Month,
                    CAST(userId AS STRING) User,
                    FROM (
                    SELECT
                    subscriber_id AS userId,
                    partitioned_at Date
                    FROM
                    `t-sports-361206.events.current_viewers`
                    WHERE
                    partitioned_at BETWEEN '".Carbon::parse($request->start_date)->format('Y-m-d')."' AND '".Carbon::parse($request->end_date)->format('Y-m-d')."'
                    UNION ALL
                    SELECT
                    subscriber_id AS userId,
                    partitioned_at date
                    FROM
                    `t-sports-361206.events.heart_beat`
                    WHERE
                    partitioned_at BETWEEN '".Carbon::parse($request->start_date)->format('Y-m-d')."' AND '".Carbon::parse($request->end_date)->format('Y-m-d')."'
                    UNION ALL
                    SELECT
                    subscriber_id AS userId,
                    partitioned_at date
                    FROM
                    `t-sports-361206.events.login_log`
                    WHERE
                    partitioned_at BETWEEN '".Carbon::parse($request->start_date)->format('Y-m-d')."' AND '".Carbon::parse($request->end_date)->format('Y-m-d')."'))
                    GROUP BY
                    Year_Month
                    ORDER BY
                    Year_Month ASC";

                $jobConfig = $this->bigQuery->query($query);
                $Results = $this->bigQuery->startQuery($jobConfig);
                $contents = $Results->queryResults();

                $i = 1;

                foreach ($contents as $row) {
                    foreach ($row as $column => $value) {
                        $reportContent[$i]['id'] = $i + 1;
                        if ($column == 'Date') {
                            $reportContent[$i][$column] = Carbon::parse($value)->format('m/d/Y');
                        } else {
                            $reportContent[$i][$column] = $value;
                        }
                        $reportContent[$i]['sl'] = $i;

                    }
                    ++$i;
                }
            }

            // Paginate the data
            $perPage = $request->get('pageSize', 10);
            $page = $request->get('pageIndex', 1);
            $total = count($reportContent);

            $paginator = new LengthAwarePaginator(
                array_slice($reportContent, ($page - 1) * $perPage, $perPage),
                $total,
                $perPage,
                $page
            );

            $totalCount = $total;
            return ApiResponse::success($paginator, $totalCount, 'Monthly Unique Users Report fetched successfully.');



        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');

        }
    }


    public function getUniqueUserDailyReport(Request $request)
    {
        try {
            $reportContent = [];
            $today = date('Y-m-d');
            $start_date = $request->start_date ?? $today;
            $end_date = $request->end_date ?? $today;

            if ($start_date || $end_date) {
                $query = "SELECT
                   Date,
                   COUNT( DISTINCT User) User,
                   FORMAT_DATE('%a', Date) as day
                   FROM (
                   SELECT
                   Date,
                   CAST(userId AS STRING) User,
                   FROM (
                   SELECT
                   subscriber_id AS userId,
                   partitioned_at Date
                   FROM
                   `t-sports-361206.events.current_viewers`
                   WHERE
                   partitioned_at BETWEEN '".Carbon::parse($start_date)->format('Y-m-d')."' AND '".Carbon::parse($end_date)->format('Y-m-d')."'
                   UNION ALL
                   SELECT
                   subscriber_id AS userId,
                   partitioned_at date
                   FROM
                   `t-sports-361206.events.heart_beat`
                   WHERE
                   partitioned_at BETWEEN '".Carbon::parse($start_date)->format('Y-m-d')."' AND '".Carbon::parse($end_date)->format('Y-m-d')."'
                   UNION ALL
                   SELECT
                   subscriber_id AS userId,
                   partitioned_at date
                   FROM
                   `t-sports-361206.events.login_log`
                   WHERE
                   partitioned_at BETWEEN '".Carbon::parse($end_date)->format('Y-m-d')."' AND '".Carbon::parse($request->end_date)->format('Y-m-d')."' ) )
                   GROUP BY
                   Date
                   ORDER BY Date DESC";

                $jobConfig = $this->bigQuery->query($query);
                $Results = $this->bigQuery->startQuery($jobConfig);
                $contents = $Results->queryResults();

                $i = 1;

                foreach ($contents as $row) {
                    foreach ($row as $column => $value) {
                        $reportContent[$i]['id'] = $i + 1;
                        if ($column == 'Date') {
                            $reportContent[$i][$column] = Carbon::parse($value)->format('m/d/Y');
                        } else {
                            $reportContent[$i][$column] = $value;
                        }
                        $reportContent[$i]['sl'] = $i;

                    }
                    ++$i;
                }
            }

            // Paginate the data
            $perPage = $request->get('pageSize', 10);
            $page = $request->get('pageIndex', 1);
            $total = count($reportContent);

            $paginator = new LengthAwarePaginator(
                array_slice($reportContent, ($page - 1) * $perPage, $perPage),
                $total,
                $perPage,
                $page
            );

            $totalCount = $total;
            return ApiResponse::success($paginator, $totalCount, 'Monthly Unique Users Report fetched successfully.');



        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');

        }
    }


    public function getLiveAudienceReport(Request $request)
    {
        try {
            $reportContent = [];
            $today = date('Y-m-d');
            $start_date = $request->input('start_date', $today);
            $end_date = $request->input('end_date', $today);

            $start = strtotime(Carbon::parse($start_date)->format('Y-m-d H:i:s'));
            $end = strtotime(Carbon::parse($end_date)->format('Y-m-d H:i:s'));
            $mins = ($end - $start) / 60;

            $minutes = $mins > 0 ? $mins : 10;

            //return $minutes;
            $query = "SELECT COUNT(DISTINCT subscriber_id) AS total_live_audience
            FROM (
                (SELECT subscriber_id, reporting_time, partitioned_at FROM `t-sports-361206.events.current_viewers`
                    WHERE partitioned_at = DATE('" . Carbon::parse($today)->format('Y-m-d') . "') AND reporting_time BETWEEN DATETIME_SUB(DATETIME_TRUNC(DATETIME(CURRENT_TIMESTAMP(), 'Asia/Dhaka'), MINUTE), INTERVAL '$minutes' MINUTE) AND DATETIME_SUB(DATETIME_TRUNC(DATETIME(CURRENT_TIMESTAMP(), 'Asia/Dhaka'), SECOND), INTERVAL 0 SECOND)
                ) UNION ALL (SELECT subscriber_id, reporting_time, partitioned_at FROM `t-sports-361206.events.heart_beat`
                    WHERE partitioned_at = DATE('" . Carbon::parse($today)->format('Y-m-d') . "') AND reporting_time BETWEEN DATETIME_SUB(DATETIME_TRUNC(DATETIME(CURRENT_TIMESTAMP(), 'Asia/Dhaka'), MINUTE), INTERVAL '$minutes' MINUTE) AND DATETIME_SUB(DATETIME_TRUNC(DATETIME(CURRENT_TIMESTAMP(), 'Asia/Dhaka'), SECOND), INTERVAL 0 SECOND)
                )
            ) AS live_audience_count;
        ";

                $jobConfig = $this->bigQuery->query($query);
                $Results = $this->bigQuery->startQuery($jobConfig);
                $contents = $Results->queryResults();

                $i = 1;

                foreach ($contents as $row) {
                    foreach ($row as $column => $value) {
                        $reportContent[$i]['id'] = $i + 1;
                        if ($column == 'Date') {
                            $reportContent[$i][$column] = Carbon::parse($value)->format('m/d/Y');
                        } else {
                            $reportContent[$i][$column] = $value;
                        }
                        $reportContent[$i]['sl'] = $i;

                    }
                    ++$i;
                }


            // Paginate the data
            $perPage = $request->get('pageSize', 10);
            $page = $request->get('pageIndex', 1);
            $total = count($reportContent);

            $paginator = new LengthAwarePaginator(
                array_slice($reportContent, ($page - 1) * $perPage, $perPage),
                $total,
                $perPage,
                $page
            );

            $totalCount = $total;
            return ApiResponse::success($paginator, $totalCount, 'Daily Live Audience Report fetched successfully.');



        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');

        }
    }


    private function getConCurrentUserDailyQuery($start_date, $end_date)
    {
        return "SELECT
                USAGE_DATE,
                USAGE_HOUR,
                TOTAL_USER
            FROM (
                SELECT
                    USAGE_DATE,
                    USAGE_HOUR,
                    TOTAL_USER,
                    RANK() OVER (ORDER BY TOTAL_USER DESC) as rnk
                FROM (
                    SELECT
                        USAGE_DATE,
                        USAGE_HOUR,
                        COUNT(DISTINCT CUSTOMER_ID) AS TOTAL_USER,

                    FROM (
                        SELECT
                            subscriber_id as CUSTOMER_ID,
                            DATE(partitioned_at) AS USAGE_DATE,
                            CAST(FORMAT_DATETIME('%H', reporting_time) AS STRING) AS USAGE_HOUR,
                            CONTENT_TYPE
                        FROM (
                            SELECT
                                subscriber_id,
                                content_id,
                                content_type,
                                device_type,
                                reporting_time,
                                partitioned_at
                            FROM
                                `t-sports-361206.events.heart_beat`
                            WHERE
                                partitioned_at BETWEEN '".Carbon::parse($start_date)->format('Y-m-d')."'
                                    AND '".Carbon::parse($end_date)->format('Y-m-d')."'
                            UNION ALL
                            SELECT
                                subscriber_id,
                                content_id,
                                content_type,
                                device_type,
                                reporting_time,
                                partitioned_at
                            FROM
                                `t-sports-361206.events.current_viewers`
                            WHERE
                                partitioned_at BETWEEN '".Carbon::parse($start_date)->format('Y-m-d')."'
                                    AND '".Carbon::parse($end_date)->format('Y-m-d')."')
                        WHERE
                            CONTENT_TYPE != 'NULL'
                    )
                    GROUP BY
                        USAGE_DATE,
                        USAGE_HOUR
                    ORDER BY
                        USAGE_DATE DESC,
                        USAGE_HOUR DESC
                ) AS inner_query
            ) AS ranked_data
            WHERE rnk = 1";
    }


    private function getConCurrentUserHourlyQuery($start_date, $end_date)
    {
        return "SELECT
                USAGE_DATE,
                USAGE_HOUR,
                COUNT(DISTINCT CUSTOMER_ID) AS TOTAL_USER
            FROM (
                SELECT
                    subscriber_id CUSTOMER_ID,
                    DATE(partitioned_at) AS USAGE_DATE,
                    CAST(FORMAT_DATETIME('%H', reporting_time) AS STRING) AS USAGE_HOUR,
                    CONTENT_TYPE
                FROM (
                    SELECT
                        subscriber_id,
                        content_id,
                        content_type,
                        device_type,
                        reporting_time,
                        partitioned_at
                    FROM
                        `t-sports-361206.events.heart_beat`
                    WHERE
                        partitioned_at BETWEEN '" . Carbon::parse($start_date)->format('Y-m-d') . "'
                        AND '" . Carbon::parse($end_date)->format('Y-m-d') . "'
                    UNION ALL
                    SELECT
                        subscriber_id,
                        content_id,
                        content_type,
                        device_type,
                        reporting_time,
                        partitioned_at
                    FROM
                        `t-sports-361206.events.current_viewers`
                    WHERE
                        partitioned_at BETWEEN '" . Carbon::parse($start_date)->format('Y-m-d') . "'
                        AND '" . Carbon::parse($end_date)->format('Y-m-d') . "')
                WHERE
                    CONTENT_TYPE != 'NULL')
            GROUP BY
                USAGE_DATE,
                USAGE_HOUR
            ORDER BY
                USAGE_DATE DESC,
                usage_hour DESC";
    }


    private function getLoginLogDailyQuery($start_date, $end_date)
    {
        return "SELECT
                USAGE_DATE,
                COUNT(login_cust_id) Total_Login,
                COUNT(DISTINCT login_cust_id) Total_Unique_login,
                COUNT(subscriber_id) Total_Hits
                FROM (
                  SELECT
                  USAGE_DATE,
                  subscriber_id,
                  login_cust_id,
                  v_customer_id,
                  a,
                  b
                  FROM (
                    SELECT
                    USAGE_DATE,
                    subscriber_id,
                    NULL AS login_cust_id,
                    subscriber_id AS v_customer_id,
                    1 AS a,
                    0 AS b
                    FROM (
                      SELECT
                      DATE(reporting_time) AS USAGE_DATE,
                      subscriber_id
                      FROM (
                        SELECT
                        reporting_time,
                        subscriber_id
                        FROM
                        `t-sports-361206.events.heart_beat`
                        WHERE
                        partitioned_at BETWEEN '" . Carbon::parse($start_date)->format('Y-m-d') . "'
                        AND '" . Carbon::parse($end_date)->format('Y-m-d') . "' ) )
                        UNION ALL
                        SELECT
                        DATE(reporting_time) AS USAGE_DATE,
                        subscriber_id AS customer_id,
                        subscriber_id AS login_cust_id,
                        NULL AS v_customer_id,
                        0 AS a,
                        1 AS b
                        FROM
                        `t-sports-361206.events.login_log`
                        WHERE
                        partitioned_at BETWEEN '" . Carbon::parse($start_date)->format('Y-m-d') . "'
                        AND '" . Carbon::parse($end_date)->format('Y-m-d') . "' ) --
                        ORDER BY
                        login_cust_id DESC )
                        GROUP BY
                        USAGE_DATE
                        ORDER BY
                        USAGE_DATE DESC";
    }

    private function getLoginLogHourlyQuery($start_date, $end_date)
    {
        return "SELECT
                USAGE_DATE,
                USAGE_HOUR,
                COUNT(subscriber_id) Total_Login,
                COUNT(DISTINCT subscriber_id) Total_Unique_login,
                COUNT(subscriber_id) Total_Hits
                FROM (
                    SELECT
                    USAGE_DATE,
                    USAGE_HOUR,
                    subscriber_id,
                    login_cust_id,
                    v_customer_id,
                    a,
                    b
                    FROM (
                        SELECT
                        USAGE_DATE,
                        USAGE_HOUR,
                        subscriber_id,
                        NULL AS login_cust_id,
                        subscriber_id AS v_customer_id,
                        1 AS a,
                        0 AS b
                        FROM (
                            SELECT
                            DATE(reporting_time) AS USAGE_DATE,
                            USAGE_HOUR,
                            subscriber_id
                            FROM (
                                SELECT
                                reporting_time,
                                CAST(FORMAT_DATETIME('%H', reporting_time) AS STRING) AS USAGE_HOUR,
                                subscriber_id
                                FROM
                                `t-sports-361206.events.heart_beat`
                                WHERE
                                partitioned_at BETWEEN '" . Carbon::parse($start_date)->format('Y-m-d') . "'
                                AND '" . Carbon::parse($end_date)->format('Y-m-d') . "'))
                                UNION ALL
                                SELECT
                                DATE(reporting_time) AS USAGE_DATE,
                                CAST(FORMAT_DATETIME('%H', reporting_time) AS STRING) AS USAGE_HOUR,
                                subscriber_id AS customer_id,
                                subscriber_id AS login_cust_id,
                                NULL AS v_customer_id,
                                0 AS a,
                                1 AS b
                                FROM
                                `t-sports-361206.events.login_log`
                                WHERE
                                partitioned_at BETWEEN '" . Carbon::parse($start_date)->format('Y-m-d') . "'
                                AND '" . Carbon::parse($end_date)->format('Y-m-d') . "' ) --
                                ORDER BY
                                login_cust_id DESC )
                                GROUP BY
                                USAGE_DATE,
                                USAGE_HOUR
                                ORDER BY
                                USAGE_DATE,
                                USAGE_HOUR ASC";
    }

    public function getPaymentData(Request $request)
    {

        try {

            $today = Carbon::now()->toDateString();
            $start_date = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::parse($today)->startOfDay();
            $end_date = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::parse($today)->endOfDay();


            $paymentMethodTotals = Subscription::selectRaw('payment_method, sum(payable_price) as total')
                ->whereIn('payment_method', ['bKash', 'GP_DOB', 'POCKET', 'ROBI_AOC'])
                ->whereBetween('start_date', [$start_date, $end_date])
                ->groupBy('payment_method')
                ->pluck('total', 'payment_method');
            $sslTotal = Subscription::whereBetween('start_date', [$start_date, $end_date])
                ->whereNotIn('payment_method', ['bKash', 'BL-DOB', 'Robi', 'ROBI_AOC', 'GP_DOB', 'POCKET', 'ROBI_AOC', 'My GP', 'BL_DATA_PACK', 'Nex Test Employee', 'TEST', 'GP DATA PACK'])
                ->whereNotNull('payment_method')
                ->sum('payable_price');

            $totalSubscriberCount = Subscriber::count();
            $todaySubscriberCount = Subscriber::whereDate('created_at', $today)->count();

            $getHeightConcurrencyUsersQuery=$this->getConCurrentUserDailyQuery($start_date,$end_date);

            $jobConfig = $this->bigQuery->query($getHeightConcurrencyUsersQuery);
            $Results = $this->bigQuery->startQuery($jobConfig);
            $contents = $Results->queryResults();

            $i = 1;

            foreach ($contents as $row) {
                foreach ($row as $column => $value) {
                    $reportContent[$i]['id'] = $i + 1;
                    if ($column == 'USAGE_DATE') {
                        $reportContent[$i][$column] = Carbon::parse($value)->format('m/d/Y');
                    } else {
                        $reportContent[$i][$column] = $value;
                    }
                    $reportContent[$i]['sl'] = $i;

                }
                ++$i;
            }

            $getHeightConcurrencyUsers=array_shift($reportContent);


            $data = [
                [
                    'title' => "Total Subscriber",
                    'value' => $totalSubscriberCount,
                    'note' => 'Filter not applicable'
                ],
                [
                    'title' => "Today's Subscriber",
                    'value' => $todaySubscriberCount,
                    'note' => 'Filter not applicable'

                ],
                [
                        'title' => "Bkash",
                        'value' => $paymentMethodTotals['bKash'] ?? 0

                ],

               [
                        'title' => "GP DOB",
                        'value' => $paymentMethodTotals['GP_DOB'] ?? 0

                ],
               [
                        'title' => "Pocket",
                        'value' => $paymentMethodTotals['POCKET'] ?? 0

                ],
               [
                        'title' => "Robi",
                        'value' => $paymentMethodTotals['ROBI_AOC'] ?? 0

                ],
                [
                    'title' => "SSL",
                    'value' => $sslTotal ?? 0

                ],
                [
                        'title' => "High. Concurrency Users",
                        'value' => $getHeightConcurrencyUsers['TOTAL_USER'] ?? 0,
                        'usage_hour' => $getHeightConcurrencyUsers['USAGE_HOUR'] ?? '00:00-00:00'

                ],

            ];



            return ApiResponse::success($data,null, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function auditReport(Request $request)
    {
        $today = Carbon::now()->toDateString();
        $start_date = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::parse($today)->startOfDay();
        $end_date = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::parse($today)->endOfDay();

        try {
            $query = Activity::leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
                ->select([
                    'users.name as user_name',
                    'activity_log.id',
                    'activity_log.log_name',
                    'activity_log.description',
                    'activity_log.subject_type',
                    'activity_log.event',
                    'activity_log.causer_id',
                    'activity_log.created_at'])->orderBy('activity_log.created_at','DESC');

            $query->whereBetween('activity_log.created_at', [$start_date, $end_date]);

            if ($request->log_name) {
                $logName = $request->input('log_name');
                $query->where('activity_log.log_name', 'like', '%' . $logName . '%');
            }

            if ($request->subject_type) {
                $query->where('activity_log.subject_type', $request->input('subject_type'));
            }

            if ($request->event) {
                $query->where('activity_log.event', $request->input('event'));
            }

            if ($request->causer_id) {
                $query->where('activity_log.causer_id', $request->input('causer_id'));
            }


            $totalCount = $query->count();

            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $auditLogs = $query->get();

            return ApiResponse::success($auditLogs, $totalCount, 'Audit report fetched successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error(500, $e->getMessage(), 'Something went wrong while fetching the audit report.');
        }
    }

    public function auditReportShowByID(Request $request)
    {
        try {
            $id = $request->query('id');
            $data = Activity::leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
                    ->select([
                    'users.name as user_name',
                    'activity_log.id',
                    'activity_log.log_name',
                    'activity_log.description',
                    'activity_log.subject_type',
                    'activity_log.subject_id',
                    'activity_log.event',
                    'activity_log.causer_id',
                    'activity_log.properties',
                    'activity_log.created_at'])->orderBy('activity_log.created_at','DESC')->findOrFail($id);

            $jsonDecodedProperties=json_decode($data->properties);

            $returnData=[
                "id"=>$data->id,
                "user_name"=>$data->user_name,
                "log_name"=>$data->log_name,
                "description"=>$data->description,
                "subject_type"=>$data->subject_type,
                "subject_id"=>$data->subject_id,
                "event"=>$data->event,
                "causer_id"=>$data->causer_id,
                "created_at"=>$data->created_at,
                "ip_address"=>$jsonDecodedProperties->ip_address??null,
                "new_data"=>$jsonDecodedProperties->new_data??null,
                "old_data"=>$jsonDecodedProperties->old_data??null,
            ];

            return ApiResponse::success($returnData, '','Resource fetched successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }


}
