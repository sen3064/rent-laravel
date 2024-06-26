<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BravoReview;
use App\Models\Tour;
use App\Models\User;
use App\Models\Boat;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;

class ReviewAPIController extends Controller
{
    public $lws = 'https://lws.pulo1000.com';
    public $wurl = 'https://m.pulo1000.com';
    public $murl = 'https://mitra.pulo1000.com';
    public $notif_data = [
        'target' => '',
        'target_role' => 'mitra',
        'target_name' => '',
        'notif_type' => [],
        'channel' => '',
        'type' => '',
        'toAdmin' => 0,
        'link' => '',
        'headings' => '',
        'message' => '',
        'player_ids' => [],
        'order' => '',
        'mail_target_type' => '',
    ];

    public function getReview(Request $request)
    {
        // dd($request->all());
        // DB::enableQueryLog();
        $reviews = BravoReview::where('object_model', $request->object_model);
        if ($request->object_model == 'tour') {
            $tour_ids = [];
            $tours = Tour::where('parent_id', $request->object_id)->get();
            foreach ($tours as $k) {
                $tour_ids[] = $k->id;
            }
            $reviews->whereIn('object_id', $tour_ids);
        } elseif ($request->object_model == 'boat') {
            $boat_ids = [];
            $boat = Boat::find($request->object_id);
            $boats = '';
            if($boat->parent_id){
                $boats = Boat::where('parent_id',$boat->parent_id)->get();
            }else{
                $boats = Boat::where('parent_id',$boat->id)->get();
            }
            foreach ($boats as $k) {
                $boat_ids[] = $k->id;
            }
            $reviews->whereIn('object_id', $boat_ids);
        }
        elseif ($request->object_model == 'food-beverages'){
            $reviews->where('vendor_id',$request->object_id);
        }
        else {
            $reviews->where('object_id', $request->object_id);
        }
        $data = $reviews->orderBy('id','desc')->with(['user'])->get();
        // dd(DB::getQueryLog());
        return response()->json([
            'success' => true,
            'message' => 'Review Fetched',
            'data' => $data
        ]);
    }

    public function addReview(Request $request)
    {
        // return response()->json(
        //     [
        //         'success'=>true,
        //         'message'=>"Terima Kasih, review anda telah ditambahkan",
        //         'data'=>$request->all()
        //     ]
        // );
        $review = new BravoReview();
        $review->booking_id = $request->booking_id;
        $review->booking_code = $request->booking_code;
        $review->object_id = $request->object_id;
        $review->object_model = $request->object_model;
        $review->content = $request->content;
        $review->rate_number = $request->rate_number;
        $review->vendor_id = $request->vendor_id;
        $review->title = $request->title ?? 'Review ' . $request->booking_code;
        $review->status = 'approved';
        $review->author_ip = $request->ip();
        $review->create_user = Auth::id();
        $review->update_user = Auth::id();
        $review->save();

        $this->token = $request->bearerToken();

        $this->sendNotif($review);
        
        return response()->json(
            [
                'success' => true,
                'message' => "Terima Kasih, review anda telah ditambahkan",
                'data' => $review
            ]
        );
    }

    public function updateReview(Request $request, $id)
    {
        $review = BravoReview::find($id);
        if ($review) {
            $review->content = $request->content;
            $review->rate_number = $request->rate_number;
            $review->save();
            $this->token = $request->bearerToken();
            $this->sendNotif($review);
            return response()->json(
                [
                    'success' => true,
                    'message' => "Terima Kasih, review anda telah berhasil diubah",
                    'data' => $review
                ]
            );
        }

        return response()->json(
            [
                'success' => false,
                'message' => "Review tidak ditemukan",
            ]
        );
    }
    
    public function sendNotif($review){
        $this->notif_data['headings'] = "Ulasan Baru";
        $this->notif_data['message'] = "Transaksi dengan Kode Pemesanan " . $review->booking_code . " telah mendapat ulasan";
        $this->notif_data['type'] = "Review";
        $this->notif_data['notif_type'] = ["in_app", "push"];
        if ($review->object_model == 'boat') {
            $boat = Boat::find($review->object_id);
            $mitra = User::find($boat->agent_id);
            $player_id_mitra = explode(',', $mitra->player_id);
            $owner = User::find($boat->create_user);
            $player_id_owner = explode(',', $owner->player_id);
            $this->notif_data['targets'][] = $mitra->id;
            $this->notif_data['targets'][] = $owner->id;
            $this->notif_data['links'][] = $this->murl . '/admin/order/' . $mitra->id;
            $this->notif_data['links'][] = $this->murl . '/admin/order/' . $owner->id;
            $this->notif_data['target_names'][] = ucfirst($mitra->first_name) . ' ' . ucfirst($mitra->last_name);
            $this->notif_data['target_names'][] = ucfirst($owner->first_name) . ' ' . ucfirst($owner->last_name);
            foreach ($player_id_mitra as $key => $val) {
                if ($val && !empty($val) && !in_array($val, $this->notif_data['player_ids'])) {
                    $this->notif_data['player_ids'][] = $val;
                }
            }
            foreach ($player_id_owner as $key => $val) {
                if ($val && !empty($val) && !in_array($val, $this->notif_data['player_ids'])) {
                    $this->notif_data['player_ids'][] = $val;
                }
            }
            $this->notif_data['channel'] = 'App\Notifications\PrivateChannelServices';
        } else {
            $mitra = User::find($review->vendor_id);
            $player_id_mitra = explode(',', $mitra->player_id);
            $this->notif_data['targets'][] = $mitra->id;
            $this->notif_data['links'][] = $this->murl . '/admin/order/' . $mitra->id;
            $this->notif_data['target_names'][] = ucfirst($mitra->first_name) . ' ' . ucfirst($mitra->last_name);
            foreach ($player_id_mitra as $key => $val) {
                if ($val && !empty($val) && !in_array($val, $this->notif_data['player_ids'])) {
                    $this->notif_data['player_ids'][] = $val;
                }
            }
            $this->notif_data['channel'] = 'App\Notifications\PrivateChannelServices';
        }
        
        $cdn = "https://bookingapidev.pulo1000.com/v2/send-notif";
		$post = Http::withToken($this->token)->withoutVerifying()->withOptions(["verify" => false])->acceptJson();
        $post->post($cdn, ["notif_data" => $this->notif_data]);
        // $response = $post->post($cdn, ["notif_data" => $this->notif_data]);
        // dd($response);
    }

    public function testSendNotif(Request $request,$id){
        $review = BravoReview::find($id);
        if($review){
            $this->token = $request->bearerToken();
            $this->sendNotif($review);
        }
    }
}
