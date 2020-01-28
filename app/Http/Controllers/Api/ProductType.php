<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;
use App\Models\ProductType as ProductTypeModel;
use Exception;
use Activity;
use Helper;
use Response;

class ProductType extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = (int) Helper::handleRequest($request, 'perPage', 10);
        $dateStart = Helper::handleRequest($request, 'dateStart');
        $dateEnd = Helper::handleRequest($request, 'dateEnd');
        $keyWord = Helper::handleRequest($request, 'keyword');

        $responseJson = [];
        $status = 200;
        $productTypes = [];
        $addOnLink = '?perPage=' . $perPage;

        try {
            $productTypes = ProductTypeModel::where('status', '>', 0);
            if(strlen($dateStart) > 0) {
                $addOnLink .= '&dateStart=' . $dateStart;
                $dateStart .= ' 00:00:00';
                $productTypes->where('created', '>=', $dateStart);
            }
            if(strlen($dateEnd) > 0) {
                $addOnLink .= '&dateE$dateEnd=' . $dateEnd;
                $dateEnd .= ' 00:00:00';
                $productTypes->where('created', '<=', $dateEnd);
            }
            if(strlen($keyWord) > 0) {
                $addOnLink .= '&keyword=' . $keyWord;
                $productTypes->where(function($query) use ($keyWord){
                    $query->orWhere('name', 'like', '%'.$keyWord.'%');
                });
            }
            $productTypes->orderBy('created_at', 'DESC');
            $productTypes = $productTypes->paginate($perPage);
            $productTypes->withPath(url('/api/product-types') . $addOnLink);
            $responseJson = Response::success('User Fetched', $productTypes);
        } catch (Exception $e) {
            Activity::addToLog('Failed Fetch List Product Type');
            $responseJson = Response::error($e->getMessage());
            $status = 500;
        }
        Activity::addToLog('Fetch List Product Type');
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
        $dataRequest['name'] = $name;
        $this->validate($dataRequest, ProductTypeModel::rule());
        DB::beginTransaction();
        try{
            $code = $this->uniqueCode(ProductTypeModel::class, 'code', 'PT', 10);
            $emailUser = auth()->user()->email;
            $data = ProductTypeModel::create([
                'code' => $code,
                'name' => $name,
                'status' => '1',
                'created_by' => $emailUser,
                'updated_by' => $emailUser,
            ]);
            $responseJson = Response::success('Data Saved', $data);
            $status = 200;
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Activity::addToLog('Failed Save Product Type' . $e->getMessage());
            $responseJson = Response::error($e->getMessage());
            $status = 500;
        }
        Activity::addToLog('Success Save Product Type');
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
            $productType = ProductTypeModel::findOrFail($id);
            $responseJson = Response::success('Show Data User', $productType);
            $status = 200;
        } catch (Exception $e) {
            $responseJson = Response::error($e->getMessage());
            $status = 500;
            Activity::addToLog('Fail Show Product Type id = '.$id);
        }
        Activity::addToLog('Show Product Type id = '.$id);
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
        $dataRequest['name'] = $name;
        $this->validate($dataRequest, ProductTypeModel::ruleUpdate($id));
        DB::beginTransaction();
        try {
            $model = ProductTypeModel::findOrFail($id);
            $model->name = $name;
            $model->updated_by = auth()->user()->email;
            $data = $model->save();
            $responseJson = Response::success('Data Saved', $model);
            $status = 200;
            Activity::addToLog('Success Update Product Types');
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $responseJson = Response::error($e->getMessage());
            $status = 500;
            Activity::addToLog('Fail Update Product Types ' . $e->getMessage());
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
            $productType = ProductTypeModel::findOrFail($id);
            $productType->status = 0;
            $productType->save();
            $responseJson = Response::success('Success deleted', $productType);
            $status = 200;
            Activity::addToLog('Success Delete Product Types ');
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $responseJson = Response::error($e->getMessage());
            $status = 500;
            Activity::addToLog('Fail Delete Product Types ' . $e->getMessage());
        }
        return response()->json($responseJson, $status);
    }
}
