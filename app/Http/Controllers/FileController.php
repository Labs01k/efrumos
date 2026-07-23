<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Models\GalleryItemId;
use App\Models\GoodsPhoto;
use App\Models\GoodsSubjectImages;
use App\Models\InfoItemId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function upload(Request $request)
    {
        if ($request->file()) {
            if (!is_null($request->input('gallery-id'))) {
                return $this->uploadGallery($request);
            } else {
                return $this->uploadOneImg($request);
            }
        }

        return response()->json([
            'status' => false,
            'messages' => controllerTrans('variables.something_wrong', LANG)
        ]);
    }

    /*public function uploadOneImg(Request $request){
        $response = [];
        $key = 0;
        $uploadPath = $request->input('uploadPath');
        foreach($request->file() as $singleFile) {
            foreach ($singleFile as $file) {
                $extension = $file->getClientOriginalExtension();
                $fileName = md5(time()) . rand(11111111, 99999999) . '.' . $extension;
                switch (strtolower($file->getClientOriginalExtension())) {
                    case 'jpg':
                    case 'png':
                    case 'svg':
                    case 'jpeg': {
                        $fileType = 'img';
                        $destinationPath = 'upfiles/' . $uploadPath;
                        break;
                    }
                    default : {
                        return response()->json([
                            'status' => false,
                            'messages' => controllerTrans('variables.invalid_img_format', $this->lang)
                        ]);
                        break;
                    }
                }

                $file->move($destinationPath, $fileName);

                if (!File::exists($destinationPath . '/s')) {
                    File::makeDirectory($destinationPath . '/s');
                }
                if (!File::exists($destinationPath . '/m')) {
                    File::makeDirectory($destinationPath . '/m');
                }

                if ($uploadPath == 'gallery') {
                	CreateImageManipulator($uploadPath, $destinationPath . '/m/', $fileName, 270, 273);
                }

                if ($uploadPath == 'banner_top') {
                    CreateImageManipulator($uploadPath, $destinationPath . '/m/', $fileName, 1150, 620);
                }

	            if ($uploadPath == 'menu') {
		            CreateImageManipulator($uploadPath, $destinationPath . '/s/', $fileName, 177, 240);
	            }
	            if ($uploadPath == 'info_line') {
		            CreateImageManipulator($uploadPath, $destinationPath . '/s/', $fileName, 400, 206);
		            CreateImageManipulator($uploadPath, $destinationPath . '/m/', $fileName, 732, 375);
	            }

                if ($uploadPath == 'admin_user') {
                    CreateImageManipulator($uploadPath, $destinationPath . '/s/', $fileName, 52, 42);
                }

//                    Image::make($destinationPath.'/'.$fileName)->resize(200,150)->save($destinationPath.'/s/'.$fileName);
//                    Image::make($destinationPath.'/'.$fileName)->resize(500,450)->save($destinationPath.'/m/'.$fileName);

                $response['fileName'][$key] = $fileName;
                $response['fileType'][$key] = $fileType;
                $response['url'][$key] = asset($destinationPath . '/' . $fileName);
                $key++;
            }
        }
        return response()->json([
            $response,
            'status'=>true
        ]);
    }*/

    /*destroy files*/
    public function destroyOneSingleImg(Request $request)
    {
        $curr_img = $request->input('curr_img');
        $curr_id = $request->input('curr_id');
        $uploadPath = $request->input('uploadPath');

        if (is_null($curr_img) || is_null($uploadPath))
            return response()->json([
                'status' => false,
                'messages' => controllerTrans('variables.something_wrong', LANG)
            ]);

        switch ($uploadPath) {
            case 'gallery-subject':
                DB::table('gallery_subject_id')
                    ->where('id', $curr_id)
                    ->update(['img' => null]);
                break;
            case 'goods-subject-meta':
                DB::table('goods_subject_id')
                    ->where('id', $curr_id)
                    ->update(['img_two' => null]);
                break;
            case 'goods-brand-palette':
                DB::table('goods_brand_id')
                    ->where('id', $curr_id)
                    ->update(['img_palette' => null]);
                break;
            case 'goods-brand-certificate':
                DB::table('goods_brand_id')
                    ->where('id', $curr_id)
                    ->update(['img_certificate' => null]);
                break;
            case 'menu':
                DB::table('menu_id')
                    ->where('id', $curr_id)
                    ->update(['img' => null]);
                break;
            case 'goods-subject':
                DB::table('goods_subject_id')
                    ->where('id', $curr_id)
                    ->update(['img' => null]);
                break;
            case 'shops':
                DB::table('shops_id')
                    ->where('id', $curr_id)
                    ->update(['img' => null]);
                break;
            case 'info-line':
                DB::table('info_line_id')
                    ->where('id', $curr_id)
                    ->update(['img' => null]);
                break;
            case 'admin-user':
                DB::table('admin_user')
                    ->where('id', $curr_id)
                    ->update(['img' => null]);
                break;
            case 'slider':
                DB::table('banner_top')
                    ->where('id', $curr_id)
                    ->update(['img' => null]);
                break;
            case 'slider-mobile':
                DB::table('banner_top')
                    ->where('id', $curr_id)
                    ->update(['img_mobile' => null]);
                break;
            case 'front-user':
                DB::table('front_user')
                    ->where('id', $curr_id)
                    ->update(['img' => null]);
                break;
            case 'social-media':
                DB::table('social_media')
                    ->where('id', $curr_id)
                    ->update(['img' => null]);
                break;
            default:
                DB::table($uploadPath)
                    ->where('id', $curr_id)
                    ->update(['img' => null]);
                break;
        }

        $destinationPath = 'upfiles/' . $uploadPath;

        if (File::exists($destinationPath . '/s/' . showImg($curr_img)))
            File::delete($destinationPath . '/s/' . showImg($curr_img));

        if (File::exists($destinationPath . '/m/' . showImg($curr_img)))
            File::delete($destinationPath . '/m/' . showImg($curr_img));

        if (File::exists($destinationPath . '/xs/' . showImg($curr_img)))
            File::delete($destinationPath . '/xs/' . showImg($curr_img));

        if (File::exists($destinationPath . '/l/' . showImg($curr_img)))
            File::delete($destinationPath . '/l/' . showImg($curr_img));

        if (File::exists($destinationPath . '/' . $curr_img))
            File::delete($destinationPath . '/' . $curr_img);

        return response()->json([
            'status' => true,
            'messages' => controllerTrans('variables.removed', LANG)
        ]);
    }

    public function destroyOneMultipleImg(Request $request)
    {
        $curr_img = $request->input('curr_img');
        $curr_id = $request->input('curr_id');
        $uploadPath = $request->input('uploadPath');

        if (is_null($uploadPath) || (is_null($curr_id) && is_null($curr_img)))
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.something_wrong', LANG)]
            ]);

        switch ($uploadPath) {
            case 'info-items':
                DB::table('info_line_images')
                    ->where('id', $curr_id)
                    ->delete();
                break;
            case 'goods-pages':
                DB::table('goods_page_images')
                    ->where('id', $curr_id)
                    ->delete();
                break;
            case 'brand':
                DB::table('goods_brand_images')
                    ->where('id', $curr_id)
                    ->delete();
                break;
            default:
                DB::table($uploadPath . '_images')
                    ->where('id', $curr_id)
                    ->delete();
                break;
        }

        if (!is_null($curr_img)) {
            $destinationPath = 'upfiles/' . $uploadPath;

            if (File::exists($destinationPath . '/s/' . showImg($curr_img)))
                File::delete($destinationPath . '/s/' . showImg($curr_img));

            if (File::exists($destinationPath . '/m/' . showImg($curr_img)))
                File::delete($destinationPath . '/m/' . showImg($curr_img));

            if (File::exists($destinationPath . '/xs/' . showImg($curr_img)))
                File::delete($destinationPath . '/xs/' . showImg($curr_img));

            if (File::exists($destinationPath . '/l/' . showImg($curr_img)))
                File::delete($destinationPath . '/l/' . showImg($curr_img));

            if (File::exists($destinationPath . '/' . $curr_img))
                File::delete($destinationPath . '/' . $curr_img);

        }

        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.removed', LANG)]
        ]);
    }

    public function activateOneImg(Request $request)
    {
        $active = $request->input('active');
        $curr_id = $request->input('curr_id');
        $uploadPath = $request->input('uploadPath');

        if (is_null($uploadPath) || is_null($curr_id))
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.something_wrong', LANG)]
            ]);

        if ($active == 1) {
            $active = 0;
            $msg = controllerTrans('variables.element_is_inactive', LANG, ['name' => '']);
        } else {
            $active = 1;
            $msg = controllerTrans('variables.element_is_active', LANG, ['name' => '']);
        }

        DB::table($uploadPath . '_images')
            ->where('id', $curr_id)
            ->update(['active' => $active]);

        return response()->json([
            'status' => true,
            'messages' => [$msg]
        ]);
    }

    public function uploadGallery(Request $request)
    {
        $uploadPath = 'goods-items';
        foreach ($request->file() as $singleFile) {
            foreach ($singleFile as $file) {
                $extension = $file->getClientOriginalExtension();
                $fileName = md5(time()) . rand(11111111, 99999999) . '.' . $extension;
                switch (strtolower($file->getClientOriginalExtension())) {
                    case 'jpg':
                    case 'png':
                    case 'jpeg':
                    case 'webp':
                        {
                            $destinationPath = 'upfiles/' . $uploadPath;
                            break;
                        }
                    default :
                        {
                            return response()->json([
                                'status' => false,
                                'messages' => [controllerTrans('variables.invalid_img_format', LANG)]
                            ]);
                            break;
                        }
                }

                $file->move($destinationPath, $fileName);

                if (!File::exists($destinationPath . '/s')) {
                    File::makeDirectory($destinationPath . '/s');
                }
                if (!File::exists($destinationPath . '/m')) {
                    File::makeDirectory($destinationPath . '/m');
                }

                CreateImageManipulator($uploadPath, $destinationPath . '/s/', $fileName, 100, 100);
                CreateImageManipulator($uploadPath, $destinationPath . '/m/', $fileName, 264, 320);

                //watermark($destinationPath.'/'.$fileName, asset('/admin-assets/img/watermark.png'), $destinationPath.'/'.$fileName);

                $maxPosition = GetMaxPosition('goods_foto');
                $data = [
                    'goods_item_id' => $request->input('gallery-id'),
                    'img' => $fileName,
                    'position' => $maxPosition + 1,
                    'active' => 1
                ];

                GoodsPhoto::create($data);
            }
        }
        return response()->json([
            'status' => true,
            'messages' => ['Save'],
            'redirect' => urlForLanguage(LANG, 'productionsphoto/' . $request->input('gallery-id'))
        ]);
    }

    public function uploadGalleryPhoto(Request $request)
    {
        $uploadPath = 'gallery-items';
        foreach ($request->file() as $singleFile) {
            foreach ($singleFile as $file) {


                $extension = $file->getClientOriginalExtension(); // getting image extension
                $fileName = md5(time()) . rand(11111111, 99999999) . '.' . $extension; // renameing image
                $original_name = $file->getClientOriginalName();

                switch (strtolower($file->getClientOriginalExtension())) {
                    case 'jpg':
                    case 'png':
                    case 'jpeg':
                        {
                            $destinationPath = 'upfiles/' . $uploadPath;
                            break;
                        }
                    default :
                        {
                            return response()->json([
                                'status' => false,
                                'messages' => [controllerTrans('variables.invalid_img_format', LANG)]
                            ]);
                            break;
                        }
                }

                $file->move($destinationPath, $fileName);

                if (!File::exists($destinationPath . '/s')) {
                    File::makeDirectory($destinationPath . '/s');
                }
                if (!File::exists($destinationPath . '/m')) {
                    File::makeDirectory($destinationPath . '/m');
                }

                CreateImageManipulator($uploadPath, $destinationPath . '/s/', $fileName, 300, 300);
                CreateImageManipulator($uploadPath, $destinationPath . '/m/', $fileName, 500, 500);

                $maxPosition = GetMaxPosition('gallery_item_id');

                $data = [
                    'gallery_subject_id' => $request->input('gallery-id'),
                    'img' => $fileName,
                    'position' => $maxPosition + 1,
                    'active' => 1,
                    'deleted' => 0,
                    'type' => 'photo',
                    'youtube_id' => '',
                    'youtube_link' => '',
                    'alias' => Str::slug(pathinfo($fileName, PATHINFO_FILENAME)),
                    'show_on_main' => 0
                ];

                $gallery_item_id = GalleryItemId::create($data);

                $data = [
                    'gallery_item_id' => $gallery_item_id->id,
                    'lang_id' => LANG_ID,
                    'name' => pathinfo($original_name, PATHINFO_FILENAME)
                ];

                GalleryItem::create($data);
            }
        }
        return response()->json([
            'status' => true,
            'messages' => ['Save'],
            'redirect' => urlForLanguage(LANG, 'itemsphoto')
        ]);
    }

    public function uploadGoodsSubjectGallery(Request $request)
    {

        $uploadPath = 'goods-subject-gallery';
        foreach ($request->file() as $singleFile) {
            foreach ($singleFile as $file) {
                $extension = $file->getClientOriginalExtension();
                $fileName = md5(time()) . rand(11111111, 99999999) . '.' . $extension;
                switch (strtolower($file->getClientOriginalExtension())) {
                    case 'jpg':
                    case 'png':
                    case 'jpeg':
                    case 'webp':
                    {
                        $destinationPath = 'upfiles/' . $uploadPath;
                        break;
                    }
                    default :
                    {
                        return response()->json([
                            'status' => false,
                            'messages' => [controllerTrans('variables.invalid_img_format', LANG)]
                        ]);
                        break;
                    }
                }

                $file->move($destinationPath, $fileName);

                if (!File::exists($destinationPath . '/s')) {
                    File::makeDirectory($destinationPath . '/s');
                }
                if (!File::exists($destinationPath . '/m')) {
                    File::makeDirectory($destinationPath . '/m');
                }

                CreateImageManipulator($uploadPath, $destinationPath . '/s/', $fileName, 300, 300);
                CreateImageManipulator($uploadPath, $destinationPath . '/m/', $fileName, 500, 500);

                //watermark($destinationPath.'/'.$fileName, asset('/admin-assets/img/watermark.png'), $destinationPath.'/'.$fileName);

                $maxPosition = GetMaxPosition('goods_subject_images');
                $data = [
                    'goods_subject_id' => $request->input('gallery-id'),
                    'img' => $fileName,
                    'position' => $maxPosition + 1,
                    'lang_id' => $request->input('current-lang-id'),
                    'active' => 1
                ];

                GoodsSubjectImages::create($data);
            }
        }
        return response()->json([
            'status' => true,
            'messages' => ['Save'],
            'redirect' => urlForLanguage(LANG, 'productionsphoto/' . $request->input('gallery-id'))
        ]);
    }
}
