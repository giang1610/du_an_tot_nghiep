<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;



class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::query();
        if($request->filled('search')){
            $query->where('name','like','%' . $request->search .'%');
        }
        $categories = $query->orderBy('id','desc')->paginate(10);
         return view('admin.category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('admin.category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        Category::create($request->validated());
        return redirect()->route('categories.index')->with('success', 'Thêm danh mục thành công');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
       
        return view('admin.category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());
        return redirect()->route('categories.index')->with('success', 'sửa danh mục thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id)
    // {
    //     //
    // }
    public function destroy($id)
{
    $category = Category::findOrFail($id);

    // Kiểm tra nếu danh mục có sản phẩm liên quan
    if ($category->products()->count() > 0) {
        return redirect()->route('categories.index')
            ->with('error', 'Không thể xóa danh mục vì đang chứa sản phẩm.');
    }

    // Nếu không có sản phẩm thì xóa
    $category->delete();

    return redirect()->route('categories.index')
        ->with('success', 'Đã chuyển vào thùng rác');
}

    public function trash()
    {
        // dd('Controller called'); // phải thấy dòng này
        $categories = Category::onlyTrashed()->get();
    //  dd($categories);
        return view('admin.category.trash', compact('categories'));
    }



    // Khôi phục
    public function restore($id)
    {
        Category::onlyTrashed()->findOrFail($id)->restore();
        return redirect()->back()->with('success', 'Khôi phục thành công');
    }

    // Xóa vĩnh viễn
    public function forceDelete($id)
    {
        Category::onlyTrashed()->findOrFail($id)->forceDelete();
        return redirect()->back()->with('success', 'Xóa vĩnh viễn thành công');
    }
    //xóa tất cả
    public function deleteAll()
    {
        Category::onlyTrashed()->forceDelete();

        return redirect()->back()->with('success', 'Đã xóa tất cả danh mục.');
    }

    public function restoreAll()
    {
        Category::onlyTrashed()->restore();

        return redirect()->back()->with('success', 'Đã khôi phục tất cả danh mục.');
    }
}
