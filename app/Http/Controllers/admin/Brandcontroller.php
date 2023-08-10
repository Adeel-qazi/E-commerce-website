<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Brandcontroller extends Controller
{

    public function index (Request $request) {

        $brands = Brand::latest();

        if(!empty($request->get('keyword'))){
            $brands = $brands->where('name','like','%'.$request->get('keyword').'%');

        }

        $brands = $brands->paginate(10);

        return view('admin.brands.list',compact('brands')); // sending categories into list of view

    }



    public function create (){
        return view('admin.brands.create');
    }


  
    public function store (Request $request) {
        
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands'
        ]);


        if($validator->passes()) {
            $brand = new Brand();
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            return response()->json([
                'status' => true,
                'message' => 'Brand Added Successfully'
            ]);
            
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }


    }


    public function edit ($brandId, Request $request){
     
        $brand = Brand::find($brandId);

        if (empty($brand)){

            $request->session()->flash('error' ,'Record Not Found');
            return redirect()->route('brands.index');
        }
        
        $data['brand'] = $brand;
        return view('admin.brands.edit',$data);


    }


    public function update ($id, Request $request){

        $brand = Brand::find($id);

        if(empty($brand)) {
            $request->session()->flash('error','Record Not Found');

               return response()->json([
                'status' => false,
                'notFound' => true,
            ]);
        }


        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'.$brand->id.',id',
        ]);


        if($validator->passes()) {
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            return response()->json([
                'status' => true,
                'message' => 'Brand Updated Successfully'
            ]);
            
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        

    }


    public function destroy ($id, Request $request) {

        $brand = Brand::find($id);

        if(empty($brand)){
            $request->session()->flash('error','Brand Not Found');

            return response()->json([
                'status' => false,
                'message' => 'Brand Not Found'
            ]);

        }

        $brand->delete();

        $request->session()->flash('success','Brand Deleted Successfully');
    
    return redirect()->json([
        'status' => true,
        'message' => 'Brand Deleted Successfully'
    ]);


    } 




}
