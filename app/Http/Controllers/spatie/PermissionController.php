<?php

namespace App\Http\Controllers\spatie;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{

    /**
     * create a new instance of the class
     *
     * @return void
     */
    /**
     * create a new instance of the class
     *
     * @return void
     */
    function __construct()
    {
        $this->middleware('permission:permission-list|permission-create|permission-edit|permission-delete', ['only' => ['store']]);
        $this->middleware('permission:permission-create', ['only' => ['store']]);
        $this->middleware('permission:permission-edit', ['only' => ['update']]);
        $this->middleware('permission:permission-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Permission::orderBy('id', 'DESC')->paginate(5);
        return response()->json([
            'permissions' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator()->make($request->all(), [
            'name' => 'required|unique:permissions,name',
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);
        $permission = Permission::create([
            'name' => $request->name
        ]);
        return response()->json([
            'message' => 'permission created successfully',
            'permission' => $permission
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $permission = Permission::find($id);
        if (isset($permission))
            return response()->json([
                'permission' => $permission
            ], 200);
        return response()->json([
            'message' => 'permission not found'
        ], 404);
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
        $validator = Validator()->make($request->all(), [
            'name' => 'required|exists:permission',
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        $permission = Permission::find($id);
        $permission->name = $request->name;
        $permission->save();

        return response()->json([
            'message' => 'permission created successfully',
            'permission' => $permission
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $permission=Permission::find($id);
        if(isset($permission))
        {
            $permission->delete();
            return response()->json([
                'message'=>'permission deleted successfully'
            ],200);
        }
        return response()->json([
            'message' => 'permission does not exist'
        ], 404);
    }
}
