<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user','product','productVariant']);
        if($request->has('search')){
            $query->where('content','like','%' . $request->search .'%');
        }
       $reviews = $query->orderBy('id','desc')->paginate(5);
       return view('admin.reviews.index', compact('reviews'));
    }

    public function show($id)
    {
        // Logic to retrieve a specific review by ID
    }

    public function store(Request $request)
    {
        // Logic to store a new review
    }

    //thay đổi trạng thái của bình luận, 1= hiển thị, 0=ẩn
    public function update($id)
    {
        $review = Review::findOrFail($id);
        $review->status = $review->status ? 0 : 1;
        $review->save();

        return redirect()->back()->with('success', 'Đã đổi trạng thái đánh giá!');
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        return redirect()->route('reviews.index')->with('success', 'Xóa bình luận thành công.');
    }
}
