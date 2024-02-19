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
use App\Models\SubCategory;
use App\Models\Tag;
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
                "tags"=>Tag::select('id as value','name as label')->orderBy('id','DESC')->get(),
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
                'transcoding_start_time',
                'transcoding_end_time',
                'is_approved',
                'is_active',
                'is_transcoded',
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
                'content_file_name',
                'transcoding_start_time',
                'transcoding_end_time',
                'is_approved',
                'is_active',
                'is_transcoded',
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
            $cdnGmcConfig =[];
            $tagIds =[];
            $request->validate([
                'content_name' => 'required|string',
                'content_description' => 'required|string',
                'content_type' => 'required|string',
                'category_id' => 'required',

            ]);

            if ($request->tags){
                $tags = $request->tags;
                $content_tags = implode(" | ", array_column($tags, 'label'));
                $tagIds = array_column($tags, 'value');

                foreach ($tags as $tag) {
                    if (is_string($tag['value'])) {
                        $newTag = Tag::create(['name' => $tag['label'], 'status' => 1]);
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

            if (!$request->hasFile('content_file_name') && !$request->content_type == 'VOD') {
                return ApiResponse::error(400, 'Content not created! Please upload a video file');
            }


$content_file_name=trim($request->content_file_name)?? '';
            if ($request->is_cdn_active){
                $cdnGmcConfig=[
                    'isActive'=>1,
                    'cdnName'=>$request->cdnName,
                    'keyName'=>$request->keyName,
                    'mediaUrl'=>$request->mediaUrl,
                    'privateKey'=>$request->privateKey,
                    'expireTimeInHours'=>$request->expireTimeInHours,
                    'signingType'=>'SIGNED_COOKIE',
                ];
            }
            $content = Content::create([
                'content_name' => $request->content_name,
                'content_description' => base64_encode($request->content_description),
                'content_type' => $request->content_type,
                'feature_banner' => $request->feature_banner,
                'mobile_logo' => $request->mobile_logo,
                'mobile_thumbnail' => $request->mobile_thumbnail,
                'web_logo' => $request->web_logo,
                'web_thumbnail' => $request->web_thumbnail,
                'stb_logo' => $request->stb_logo,
                'stb_thumbnail' => $request->stb_thumbnail,
                'duration' => $request->duration,
                'is_active' => $request->is_active??0,
                'is_approved' => 1,
                'is_ad_active' => 1,
                'category_id' => $request->category_id,
                'category_name' => $request->category_name,
                'sub_category_id' => $request->sub_category_id??0,
                'sub_category_name' => $request->sub_category_name??'',
                'orientation' => $request->orientation ?? '',
                'is_horizontal' => $request->orientation,
                'cdn_gmc_conf' =>$request->is_cdn_active === true?$cdnGmcConfig:null,
                'content_expire_time' => '2040-01-01 00:00:01',
                'content_publish_time' => $request->content_publish_time??now(),
                'is_premium' => $request->is_premium??0,
                'content_file_name' => $content_file_name,
                'bucket_content_name' => $content_file_name,
                'content_drm_dash_url' => $request->content_drm_dash_url??'',
                'content_drm_hls_url' => $request->content_drm_hls_url??'',
                'content_owner_id' => 1,
                'content_owner_name' => 'T Sports',
                'is_drm_active' => $request->is_drm_active??0,
                'tags' => $content_tags??' ',
                'created_by' => Auth::guard('sanctum')->user()->id
            ]);

            $lastid = $content->id;
            $content_dir = md5($lastid . Carbon::now());
            $content->content_dir = $content_dir;
            $content->share_url = md5($lastid . Carbon::now() . rand(10, 100));

            if (empty($request->content_aes_128_hls_url) || $request->content_aes_128_hls_url == null) {
                $content->content_aes_128_hls_url = 'https://vod.tsports.com/' . $content_dir . '/manifest.m3u8';
            } else {
                $content->content_aes_128_hls_url = $request->content_aes_128_hls_url;
            }

            $content->save();

            Content::where('id', $content->id)
                ->update(['is_horizontal' => $content->orientation, 'bucket_content_name' => $content->content_file_name]);

            if ($content){
                Logger::createLog( $content->content_name,'create','Content',$request->all());
            }

            //return $tagIds;
            $content->tags()->sync($tagIds);
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
            if (!empty($request->tags)) {
                $tags = $request->tags;
                $content_tags = implode(" | ", array_column($tags, 'label'));
          ;
            }
            $oldData = $content->getAttributes();
            if ($request->is_cdn_active){
                $cdnGmcConfig=[
                    'isActive'=>1,
                    'cdnName'=>$request->cdnName??null,
                    'keyName'=>$request->keyName??null,
                    'mediaUrl'=>$request->mediaUrl??null,
                    'privateKey'=>$request->privateKey??null,
                    'expireTimeInHours'=>$request->expireTimeInHours??null,
                    'signingType'=>'SIGNED_COOKIE',
                ];
            }

            // Update content attributes based on the request
            $content->update([
                'content_name' => $request->content_name,
                'content_description' => base64_encode($request->content_description),
                'content_type' => $request->content_type,
                'feature_banner' => $request->feature_banner,
                'mobile_logo' => $request->mobile_logo,
                'mobile_thumbnail' => $request->mobile_thumbnail,
                'web_logo' => $request->web_logo,
                'web_thumbnail' => $request->web_thumbnail,
                'stb_logo' => $request->stb_logo,
                'stb_thumbnail' => $request->stb_thumbnail,
                'category_id' => $request->category_id,
                'category_name' => $request->category_name,
                'sub_category_id' => $request->sub_category_id?? 0,
                'sub_category_name' => $request->sub_category_name??'',
                'orientation' => $request->orientation ?? '',
                'is_premium' => $request->is_premium ?? 0,
                'is_active' => $request->is_active ?? 0,
                'content_file_name' => $request->content_file_name ?? '',
                'duration' => $request->duration,
                'cdn_gmc_conf' =>$request->is_cdn_active ?$cdnGmcConfig:null,
                'content_drm_dash_url' => $request->content_drm_dash_url?? '',
                'content_drm_hls_url' => $request->content_drm_hls_url ?? '',
                'tags' => $content_tags?? ' ',
                'bucket_content_name' => $request->content_file_name?? '',
                'is_horizontal' => $request->orientation?? '',
                'is_drm_active' => $request->is_drm_active?? 0,
            ]);

            if (!empty($request->tags)) {
                $tagids = array_column($request->tags, 'value');
                if (is_int($tagids)){
                    $content->tags()->sync($tagids);

                }
            }
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
