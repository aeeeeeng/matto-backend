<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;
use App\Models\Uom as UomModel;
use Exception;
use Activity;
use Helper;
use Response;

class UomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    const __THIS_CONTROLLER_API = 'Uom';

    public function index(Request $request)
    {
        $perPage = (int) Helper::handleRequest($request, 'perPage', 10);
        $dateStart = Helper::handleRequest($request, 'dateStart');
        $dateEnd = Helper::handleRequest($request, 'dateEnd');
        $keyWord = Helper::handleRequest($request, 'keyword');

        $responseJson = [];
        $status = 200;
        $uom = [];
        $addOnLink = '?perPage=' . $perPage;

        try {
            $uom = UomModel::where('status', '>', 0);
            if(strlen($dateStart) > 0) {
                $addOnLink .= '&dateStart=' . $dateStart;
                $dateStart .= ' 00:00:00';
                $uom->where('created_at', '>=', $dateStart);
            }
            if(strlen($dateEnd) > 0) {
                $addOnLink .= '&dateEnd=' . $dateEnd;
                $dateEnd .= ' 23:59:59';
                $uom->where('created_at', '<=', $dateEnd);
            }
            if(strlen($keyWord) > 0) {
                $addOnLink .= '&keyword=' . $keyWord;
                $uom->where(function($query) use ($keyWord){
                    $query->orWhere('name', 'like', '%'.$keyWord.'%');
                    $query->orWhere('code', 'like', '%'.$keyWord.'%');
                });
            }
            $uom->orderBy('created_at', 'DESC');
            $uom = $uom->paginate($perPage);
            $uom->withPath(url('/api/uom') . $addOnLink);
            $responseJson = Response::success(self::__THIS_CONTROLLER_API . ' Fetched', $uom);
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
        $dataRequest = [];
        $dataRequest['name'] = $name;
        $dataRequest['full_name'] = $full_name;
        $this->validate($dataRequest, UomModel::rule());
        DB::beginTransaction();
        try {
            $code = $this->uniqueCode(UomModel::class, 'code', 'UOM', 10);
            $emailUser = auth()->user()->email;
            $data = UomModel::create([
                'code' => $code,
                'name' => strtoupper($name),
                'full_name' => ucwords($full_name),
                'status' => 1,
                'created_by' => $emailUser,
                'updated_by' => $emailUser
            ]);
            $responseJson = Response::success('Data Saved', $data);
            $status = 200;
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Activity::addToLog('Failed Save ' . self::__THIS_CONTROLLER_API . $e->getMessage());
            $responseJson = Response::error($e->getMessage());
            $status = 500;
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
            $uom = UomModel::findOrFail($id);
            $responseJson = Response::success('Show Data ' . self::__THIS_CONTROLLER_API, $uom);
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
        $dataRequest['name'] = $name;
        $dataRequest['full_name'] = $full_name;
        $this->validate($dataRequest, UomModel::ruleUpdate($id));
        DB::beginTransaction();
        try {
            $model = UomModel::findOrFail($id);
            $model->name = strtoupper($name);
            $model->full_name = ucwords($full_name);
            $model->updated_by = auth()->user()->email;
            $model->save();
            $responseJson = Response::success('Data Saved', $model);
            $status = 200;
            Activity::addToLog('Success Update ' . self::__THIS_CONTROLLER_API);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $responseJson = Response::error($e->getMessage());
            $status = 500;
            Activity::addToLog('Fail Update ' . self::__THIS_CONTROLLER_API . $e->getMessage());
        }
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
            $model = UomModel::findOrFail($id);
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
