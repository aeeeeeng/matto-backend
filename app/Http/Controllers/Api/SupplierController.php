<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;
use App\Models\Supplier;
use Exception;
use Activity;
use Helper;
use Response;
use App\Traits\FileTrait;
use Str;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     use FileTrait;

    const __THIS_CONTROLLER_API = 'Supplier';

    public function index(Request $request)
    {
        $perPage = (int) Helper::handleRequest($request, 'perPage', 10);
        $dateStart = Helper::handleRequest($request, 'dateStart');
        $dateEnd = Helper::handleRequest($request, 'dateEnd');
        $keyWord = Helper::handleRequest($request, 'keyword');

        $responseJson = [];
        $status = 200;
        $result = [];
        $addOnLink = '?perPage=' . $perPage;

        try {
            $result = Supplier::where('status', '>', 0);
            if(strlen($dateStart) > 0) {
                $addOnLink .= '&dateStart=' . $dateStart;
                $dateStart .= ' 00:00:00';
                $result->where('created_at', '>=', $dateStart);
            }
            if(strlen($dateEnd) > 0) {
                $addOnLink .= '&dateEnd=' . $dateEnd;
                $dateEnd .= ' 23:59:59';
                $result->where('created_at', '<=', $dateEnd);
            }
            if(strlen($keyWord) > 0) {
                $addOnLink .= '&keyword=' . $keyWord;
                $result->where(function($query) use ($keyWord){
                    $query->orWhere('name', 'like', '%'.$keyWord.'%');
                    $query->orWhere('full_name', 'like', '%'.$keyWord.'%');
                    $query->orWhere('code', 'like', '%'.$keyWord.'%');
                });
            }
            $result->orderBy('created_at', 'DESC');
            $result = $result->paginate($perPage);
            $result->withPath(url('/api/uom') . $addOnLink);
            $responseJson = Response::success(self::__THIS_CONTROLLER_API . ' Fetched', $result);
            $status = 200;
        } catch (Exception $e) {
            Activity::addToLog('Failed Fetch List ' . self::__THIS_CONTROLLER_API);
            $responseJson = Response::error($e->getMessage());
            $status = 500;
        }

        Activity::addToLog('Fetch List ' . self::__THIS_CONTROLLER_API);
        return response()->json($responseJson, $status);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $status = 200;
        $responseJson = [];
        $name = Helper::handleRequest($request, 'name');
        $full_name = Helper::handleRequest($request, 'full_name');
        $address = Helper::handleRequest($request, 'address');
        $emailUser = auth()->user()->email;
        $code = $this->uniqueCode(Supplier::class, 'code', 'SUP', 10);
        $dataRequest = [];
        $dataRequest['code'] = $code;
        $dataRequest['name'] = $name;
        $dataRequest['full_name'] = $full_name;
        $dataRequest['address'] = $address;
        $dataRequest['status'] = 1;
        $dataRequest['created_by'] = $emailUser;
        $dataRequest['updated_by'] = $emailUser;
        if ($request->has('image_upload')) {
            $image = $request->file('image_upload');
            $dataRequest['image_upload'] = $image;
            $name = Str::slug($name).'-'.time();
            $folder = 'uploads/images/supplier/';
            $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
            $dataRequest['image_dir'] = $filePath;
            $dataRequest['image'] = url($filePath);
            $this->validate($dataRequest, Supplier::rule());
            try {
                $this->uploadImage($image, $folder, 'public', $name);
            } catch (Exception $e) {
                Activity::addToLog('Failed Upload Image ' . self::__THIS_CONTROLLER_API . $e->getMessage());
                $responseJson = Response::error($e->getMessage());
                $status = 500;
            }
        } else {
            $this->validate($dataRequest, Supplier::rule());
        }
        if($status === 200) {
            DB::beginTransaction();
            try {
                $data = Supplier::create($dataRequest);
                $responseJson = Response::success('Data Saved', $data);
                $status = 200;
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                Activity::addToLog('Failed Save ' . self::__THIS_CONTROLLER_API . $e->getMessage());
                $responseJson = Response::error($e->getMessage());
                $status = 500;
            }
        }
        Activity::addToLog('Success Save ' . self::__THIS_CONTROLLER_API);
        return response()->json($responseJson, $status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $status = 200;
        $responseJson = [];
        try {
            $result = Supplier::findOrFail($id);
            $responseJson = Response::success('Show Data ' . self::__THIS_CONTROLLER_API, $result);
            $status = 200;
        } catch (Exception $e) {
            $responseJson = Response::error($e->getMessage());
            $status = 500;
            Activity::addToLog('Fail Show ' . self::__THIS_CONTROLLER_API . ' id = '.$id);
        }
        Activity::addToLog('Show ' . self::__THIS_CONTROLLER_API . ' id = '.$id);
        return response()->json($responseJson, $status);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $status = 200;
        $responseJson = [];
        $name = Helper::handleRequest($request, 'name');
        $full_name = Helper::handleRequest($request, 'full_name');
        $address = Helper::handleRequest($request, 'address');
        $emailUser = auth()->user()->email;

        $dataRequest = [];
        $dataRequest['name'] = $name;
        $dataRequest['full_name'] = $full_name;
        $dataRequest['address'] = $address;
        $dataRequest['status'] = 1;
        $dataRequest['updated_by'] = $emailUser;
        if ($request->has('image_upload')) {
            $image = $request->file('image_upload');
            $dataRequest['image_upload'] = $image;
            $name = Str::slug($name).'-'.time();
            $folder = 'uploads/images/supplier/';
            $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
            $dataRequest['image_dir'] = $filePath;
            $dataRequest['image'] = url($filePath);
            $this->validate($dataRequest, Supplier::ruleUpdate($id));
            try {
                $dataSupplier = Supplier::find($id);
                $imageDir = $dataSupplier->image_dir;
                $this->deleteImage($imageDir);
            } catch (Exception $e) {
                Activity::addToLog('Failed Delete Image ' . self::__THIS_CONTROLLER_API . $e->getMessage());
                $responseJson = Response::error($e->getMessage());
                $status = 500;
            }

            if($status === 200) {
                try {
                    $this->uploadImage($image, $folder, 'public', $name);
                } catch (Exception $e) {
                    Activity::addToLog('Failed Upload Image ' . self::__THIS_CONTROLLER_API . $e->getMessage());
                    $responseJson = Response::error($e->getMessage());
                    $status = 500;
                }
            }

        } else {
            $this->validate($dataRequest, Supplier::ruleUpdate($id));
        }
        if($status === 200) {
            DB::beginTransaction();
            try {
                $data = Supplier::findOrFail($id);
                $data->update($dataRequest);
                $responseJson = Response::success('Data Updated', $data);
                $status = 200;
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                Activity::addToLog('Failed Updated ' . self::__THIS_CONTROLLER_API . $e->getMessage());
                $responseJson = Response::error($e->getMessage());
                $status = 500;
            }
        }
        Activity::addToLog('Success Updated ' . self::__THIS_CONTROLLER_API);
        return response()->json($responseJson, $status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $status = 200;
        $responseJson = [];
        DB::beginTransaction();
        try {
            $model = Supplier::findOrFail($id);
            $model->status = 0;
            $model->save();
            $responseJson = Response::success('Success deleted', $model);
            $status = 200;
            Activity::addToLog('Success Delete ' . self::__THIS_CONTROLLER_API);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $responseJson = Response::error($e->getMessage());
            $status = 500;
            Activity::addToLog('Fail Delete ' . self::__THIS_CONTROLLER_API . $e->getMessage());
        }
        return response()->json($responseJson, $status);
    }
}
