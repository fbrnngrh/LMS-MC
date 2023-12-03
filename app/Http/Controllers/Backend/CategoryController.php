<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    //
    public function AllCategory(){
        $category = Category::latest()->get();
        return view('admin.backend.category.all_category', compact('category'));
    }

    public function AddCategory(){
        return view('admin.backend.category.add_category');
    }

    public function StoreCategory(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'category_name' => 'required|unique:categories|max:255',
            'image' => 'required|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $image = $request->file('image');  
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        Image::make($image)->resize(370,246)->save('upload/category/'.$name_gen);
        $save_url = 'upload/category/'.$name_gen;

        Category::insert([
            'category_name' => $request->category_name,
            'category_slug' => strtolower(str_replace(' ','-',$request->category_name)),
            'image' => $save_url,        

        ]);

        $notification = array(
            'message' => 'Category Inserted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.category')->with($notification);  
    }

    public function EditCategory($id){
        $category = Category::find($id);
        return view('admin.backend.category.edit_category', compact('category'));
    }

    public function UpdateCategory(Request $request)
    {

        $cat_id = $request->id;
        // Validasi input
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $category = Category::find($cat_id);
    
        // Periksa apakah ada file gambar yang diunggah
        if ($request->hasFile('image')) {
            // Hapus gambar lama
            if (file_exists($category->image)) {
                unlink($category->image);
            }
    
            // Proses dan simpan gambar baru
            $image = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(370, 246)->save('upload/category/' . $name_gen);
            $save_url = 'upload/category/' . $name_gen;
    
            // Update data kategori dengan URL gambar baru
            $category->update([
                'category_name' => $request->category_name,
                'category_slug' => strtolower(str_replace(' ', '-', $request->category_name)),
                'image' => $save_url,
            ]);
    
            $message = 'Category Updated With Image Successfully';
        } else {
            // Update data kategori tanpa gambar baru
            $category->update([
                'category_name' => $request->category_name,
                'category_slug' => strtolower(str_replace(' ', '-', $request->category_name)),
            ]);
    
            $message = 'Category Updated Without Image Successfully';
        }
    
        $notification = [
            'message' => $message,
            'alert-type' => 'success',
        ];
    
        return redirect()->route('all.category')->with($notification);
    }

    public function DeleteCategory($id){
        $category = Category::find($id);
        $image = $category->image;
        if(file_exists($image)){
            unlink($image);
        }
        $category->delete();
        $notification = array(
            'message' => 'Category Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.category')->with($notification);  
    }

    // SubCategory Function

    public function AllSubCategory(){
        $subcategory = SubCategory::latest()->get();
        return view('admin.backend.subcategory.all_subcategory', compact('subcategory'));
    }

    public function AddSubCategory(){
        $category = Category::latest()->get();
        return view('admin.backend.subcategory.add_subcategory', compact('category'));
    }

    public function StoreSubCategory(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'subcategory_name' => 'required|unique:sub_categories|max:255',
        ]);

        SubCategory::insert([
            'category_id' => $request->category_id,
            'subcategory_name' => $request->subcategory_name,
            'subcategory_slug' => strtolower(str_replace(' ','-',$request->subcategory_name)),        

        ]);

        $notification = array(
            'message' => 'SubCategory Inserted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.subcategory')->with($notification);  
    }

    public function EditSubCategory($id){
        $category = Category::latest()->get();
        $subcategory = SubCategory::find($id);
        return view('admin.backend.subcategory.edit_subcategory', compact('category','subcategory'));
    }

    public function UpdateSubCategory(Request $request)
    {

        $subcat_id = $request->id;
        // Validasi input
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'subcategory_name' => 'required|string|max:255',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $subcategory = SubCategory::find($subcat_id);
    
        // Update data kategori tanpa gambar baru
        $subcategory->update([
            'category_id' => $request->category_id,
            'subcategory_name' => $request->subcategory_name,
            'subcategory_slug' => strtolower(str_replace(' ', '-', $request->subcategory_name)),
        ]);
    
        $notification = [
            'message' => 'SubCategory Updated Successfully',
            'alert-type' => 'success',
        ];
    
        return redirect()->route('all.subcategory')->with($notification);
    }

    public function DeleteSubCategory($id){
        $subcategory = SubCategory::find($id)->delete();
        
        $notification = array(
            'message' => 'SubCategory Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.subcategory')->with($notification);  
    }
}
