<?php

namespace App\Http\Controllers;
use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\Logger;
use App\Http\Resources\ContentResource;
use App\Http\Resources\FeaturedContentCollection;
use App\Http\Resources\FeaturedContentResource;
use App\Models\Category;
use App\Models\Content;
use App\Models\FeaturedContent;
use App\Models\Interest;
use App\Models\SubCategory;
use App\Models\Tag;
use App\Models\WebSeries;
use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    public function __construct() {
        $this->privateKeyFileContent = Config::get('app.google_privateKeyFileContent');
        $this->assetBucketName = Config::get('app.cloud_assetBucketName');
        $this->videoBucketName = Config::get('app.cloud_videoBucketName');
    }
    public function getLiveContentDropdown(Request $request)
    {

        try {
            $list=Content::select('id as value','content_name as label')->orderBy('id','DESC')->where('is_active',1)->get();

            return ApiResponse::success($list,null, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function getDownloadList(Request $request)
    {
        try {

            $query = Content::select([
                'id',
                'content_name',
                'category_name',
                'content_file_name',
                'transcoding_start_time',
                'transcoding_end_time',
                'is_approved',
                'is_active',
                'is_transcoded',
                'is_premium',
                'created_at',
                'updated_at',
            ])->where(['content_type'=> 'VOD','is_active'=>1,'is_transcoded'=>0])->orderBy('id','DESC');

            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $contents = $query->get();
            $totalCount = Content::where(['content_type'=> 'VOD','is_active'=>1,'is_transcoded'=>0])->count();

            return ApiResponse::success($contents,$totalCount, 'Resource fetched successfully.');
        }catch (\Throwable $e){
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');

        }


    }

    public function downloadVod(Request $request) {
        $id = $request->query('id');

        $contentInfo = Content::find($id);
        $bucketName = 'tsports-ugc-storage';
        $objectName = $contentInfo->content_file_name;
        try {
            $storage = new StorageClient([
                'keyFile' => json_decode($this->privateKeyFileContent, true)
            ]);
        } catch (Exception $e) {
            // maybe invalid private key ?
            print $e;
            return;
        }

        $bucket = $storage->bucket($bucketName);
        $object = $bucket->object($objectName);

        $url = $object->signedUrl(
        # This URL is valid for 15 minutes
            new \DateTime('50 min'),
            [
                'version' => 'v4',
            ]
        );
        echo $url;
        echo "<br>";
        echo "<br>";
        echo "<br>";
        echo '<a style="color:green; font-weight:bold; text-align:center" href="'.$url.'"> Click here to download file</a>';
    }


    public function getContentHomeData(Request $request)
    {

        try {

            $data=[
                "categories"=>Category::select('id as value','category_name as label')->with('subCategories')->orderBy('id','DESC')->where('is_active',1)->get(),
                "subCategories"=>SubCategory::select('id as value','category_id','sub_category_name as label')->orderBy('id','DESC')->where('is_active',1)->get(),
                "interests"=>Interest::select('id as value','interest_name as label')->orderBy('id','DESC')->get(),
                "webSeries"=>WebSeries::select('id as value','series_name as label')->orderBy('id','DESC')->get(),
                "orientations"=>[
                    ["label" => 'Undefined', "value" => 0],
                    ["label" => 'Vertical', "value" => 1],
                    ["label" => 'Horizontal', "value" => 2],
                ]


            ];

            return ApiResponse::success($data,null, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function getLiveContents(Request $request)
    {
        try {
            $query = Content::select([
                'id',
                'content_name',
                'category_name',
                'content_identity',
                'is_active',
                'is_premium',
                'created_at',
                'updated_at',
            ])->where(['content_type'=> 'LIVE'])->orderBy('id','DESC');

            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $contents = $query->get();
            $totalCount = Content::where(['content_type'=> 'LIVE'])->count();

            return ApiResponse::success($contents,$totalCount, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function getVodContents(Request $request)
    {
        try {
            $query = Content::select([
                'id',
                'content_name',
                'category_name',
                'content_identity',
                'is_tv_series',
                'is_active',
                'is_premium',
                'created_at',
                'updated_at',
            ])->where(['content_type'=> 'VOD'])->orderBy('id','DESC');

            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $contents = $query->get();
            $totalCount = Content::where(['content_type'=> 'VOD'])->count();

            return ApiResponse::success($contents,$totalCount, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function getFeaturedContents(Request $request)
    {
        try {
            $query = FeaturedContent::select([
                'id',
                'content_name',
                'category_name',
                'transcoding_start_time',
                'transcoding_end_time',
                'is_approved',
                'is_active',
                'is_transcoded',
                'is_premium',
                'created_at',
                'updated_at',
            ])->orderBy('id','DESC');
            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $contents = $query->get();
           // $query = FeaturedContentResource::collection(FeaturedContent::orderBy('id','DESC'))->get();

            $totalCount = FeaturedContent::count();
            return ApiResponse::success($contents, $totalCount,'Contents fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');

        }

    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $tags=[];
            $content_tags =" ";
            $tagIds =[];
            $request->validate([
                'content_name' => 'required|string',
                'content_description' => 'required|string',
                'content_type' => 'required|string',
                'category_id' => 'required',

            ]);

            if ($request->genre){
                $tags = $request->genre;
                $content_tags = implode(",", array_column($tags, 'label'));
                $tagIds = array_column($tags, 'value');

                foreach ($tags as $tag) {
                    if (is_string($tag['value'])) {
                        $newTag = Interest::create(['interest_name' => $tag['label'], 'status' => 1]);
                        $tagIds[] = $newTag->id;
                    } else {
                        $tagIds[] = $tag['value'];
                    }
                }

                $tagIds = array_filter($tagIds, function ($value) {
                    return is_numeric($value); // Keep only numeric values (tag ids)
                });

                $tagIds = array_unique($tagIds);
                $tagIds = array_values($tagIds);
            }

               // return $tagIds;
            if (empty($request->category_id)) {
                return ApiResponse::error(400, 'Content not created! Please select a category');
            }



            $content = Content::create([
                'content_name' => $request->content_name,
                'content_description' => base64_encode($request->content_description),
                'content_type' => $request->content_type,
                'content_identity' => $request->content_identity,
                'is_trailer_available' => $request->is_trailer_available??0,
                'trailer_uri' => $request->trailer_uri,
                'quality' => $request->quality,
                'rating' => $request->rating,
                'genre' => $content_tags??'',
                'category_id' => $request->category_id,
                'category_name' => $request->category_name,
                'subcategory_id' => $request->sub_category_id??0,
                'subcategory_name' => $request->sub_category_name??'',
                'release_year' => $request->release_year,
                'release_date' => $request->release_date,
                'language' => $request->language,
                'poster' => $request->poster,
                'backdrops_Poster' => $request->backdrops_Poster,
                'home_page_link' => $request->home_page_link,
                'runtime' => $request->runtime,
                'actors' => $request->actors,
                'size' => $request->size,
                'file_location' => $request->file_location,
                'last_air_date' => $request->last_air_date,
                'keywords' => $request->keywords,
                'is_tv_series' => $request->is_tv_series??0,
                'tv_series_id' => $request->tv_series_id,
                'tv_series_name' => $request->tv_series_name,
                'season_number' => $request->season_number,
                'episode_number' => $request->episode_number,
                'episode_identity' => $request->episode_identity,
                'overview' => $request->overview,
                'is_active' => $request->is_active??0,
                'is_adult' => $request->is_adult??0,
                'is_premium' => $request->is_premium??0,
                'created_by' => Auth::guard('sanctum')->user()->id
            ]);


            $content->save();


            if ($content){
                Logger::createLog( $content->content_name,'create','Content',$request->all());
            }

            //return $tagIds;
           // $content->interest()->sync($tagIds);
            DB::commit();
          // event(new Registered($content));

            return ApiResponse::success($content, null,'Content created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(500, $e->getMessage(), 'Something went wrong!');
        }
    }

    public function destroy(Request $request)
    {
        try {

            $ids = $request->input('id');

            if (is_array($ids)) {
                // Batch delete
                Content::whereIn('id', $ids)->update(['is_active' => 0, 'updated_by' => Auth::guard('sanctum')->user()->id]);
            } else {
                // Single delete
                $content = Content::find($ids);

                if (!$content) {
                    return ApiResponse::error(404, 'Content not found', 'The specified content does not exist.');
                }

                if ($content->is_active == 0) {
                    return ApiResponse::error(400, 'Bad Request', 'The content is already inactive.');
                }

                $content->update(['is_active' => 0, 'updated_by' => Auth::guard('sanctum')->user()->id]);
                if ($content){
                    Logger::createLog( $content->content_name,'delete','Content',$request->all());
                }
            }
            return ApiResponse::success([], null, 'Content deleted successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function show(Request $request)
    {
        try {
            $id = $request->query('id');
            $content = Content::findOrFail($id);

            // Use the ContentResource to transform the content data
            $resource = new ContentResource($content);

            // Modify the response format based on your ApiResponse class
            return ApiResponse::success($resource, '','Content fetched successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $tags = [];
            $content_tags = " ";
            $cdnGmcConfig = [];
            $id = $request->id;


            // Validation can be added back if needed
            $request->validate([
                'content_name' => 'required|string',
                'content_description' => 'required|string',
                'content_type' => 'required|string',
                'category_id' => 'required',
            ]);

            $content = Content::findOrFail($id);
            // Update tags if provided
            if (!empty($request->genre)) {
                $tags = $request->genre;
                $content_tags = implode(",", array_column($tags, 'label'));
          ;
            }
            $oldData = $content->getAttributes();


            // Update content attributes based on the request
            $content->update([
                'content_name' => $request->content_name,
                'content_description' => base64_encode($request->content_description),
                'content_type' => $request->content_type,
                'content_identity' => $request->content_identity,
                'is_trailer_available' => $request->is_trailer_available??0,
                'trailer_uri' => $request->trailer_uri,
                'quality' => $request->quality,
                'rating' => $request->rating,
                'genre' => $content_tags??'',
                'category_id' => $request->category_id,
                'category_name' => $request->category_name,
                'subcategory_id' => $request->sub_category_id??0,
                'subcategory_name' => $request->sub_category_name??'',
                'release_year' => $request->release_year,
                'release_date' => $request->release_date,
                'language' => $request->language,
                'poster' => $request->poster,
                'backdrops_Poster' => $request->backdrops_Poster,
                'home_page_link' => $request->home_page_link,
                'runtime' => $request->runtime,
                'actors' => $request->actors,
                'size' => $request->size,
                'file_location' => $request->file_location,
                'last_air_date' => $request->last_air_date,
                'keywords' => $request->keywords,
                'is_tv_series' => $request->is_tv_series??0,
                'tv_series_id' => $request->tv_series_id,
                'tv_series_name' => $request->tv_series_name,
                'season_number' => $request->season_number,
                'episode_number' => $request->episode_number,
                'episode_identity' => $request->episode_identity,
                'overview' => $request->overview,
                'is_active' => $request->is_active??0,
                'is_adult' => $request->is_adult??0,
                'is_premium' => $request->is_premium??0,
                'updated_by' => Auth::guard('sanctum')->user()->id
            ]);



//            if (!empty($request->tags)) {
//                $tagids = array_column($request->genre, 'value');
//                if (is_int($tagids)){
//                    $content->tags()->sync($tagids);
//
//                }
//            }
            if ($content){
                Logger::createLog( $content->content_name,'update','Content',$request->all(),$oldData);
            }
            DB::commit();
            return ApiResponse::success($content, null, 'Content updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }





}
