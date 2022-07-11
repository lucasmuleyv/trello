<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:300',
            'description' => 'required|max:65535',
            'order' => 'integer',
            'list_id' => 'required|integer'
        ]);
        $card = new Card();
        $card->name = $request->name;
        $card->description = $request->description;
        $card->order = $request->order ?? 1;
        $card->list_id = $request->list_id;
        $card->save();
        return response()->json($card);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $validated = $request->validate([
            'name' => 'required|max:300',
            'description' => 'required|max:65535',
            'order' => 'integer',
            'list_id' => 'required|integer'
        ]);
        $card = Card::find($id);
        if(!$card){
            return response()->json(['success' => false],404);
        }
        if(isset($request->order)){
            if($request->order != $card->order || $request->list_id != $card->list_id){
                if($request->list_id == $card->list_id){
                    // We change the order. We need to update the other cards with the correct order.
                    if($request->order < $card->order){
                        $cards_increment = Card::where('list_id', $request->list_id)->where('order','>=',$request->order)->where('order','<',$card->order)->get();
                        foreach($cards_increment as $c){
                            $c->order = $c->order + 1;
                            $c->save();
                        }
                    }
                    if($request->order > $card->order){
                        $cards_decrement = Card::where('list_id', $request->list_id)->where('order','>',$card->order)->where('order','<=',$request->order)->get();         
                        foreach($cards_decrement as $c){
                            $c->order = $c->order - 1;
                            $c->save();
                        }
                    }
                }else{
                    // We change the order. We need to update the other cards with the correct order.
                    $cards_increment = Card::where('list_id', $request->list_id)->where('order','>=',$request->order)->get();
                    foreach($cards_increment as $c){
                        $c->order = $c->order + 1;
                        $c->save();
                    }
                    $cards_decrement = Card::where('list_id', $card->list_id)->where('order','>',$card->order)->get();         
                    foreach($cards_decrement as $c){
                        $c->order = $c->order - 1;
                        $c->save();
                    }
                }
            }
        }
        $card->name = $request->name;
        $card->description = $request->description;
        $card->order = $request->order ?? 1;
        $card->list_id = $request->list_id;
        $card->save();
        return response()->json($card);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $card = Card::find($id);
        if(!$card)
            return response()->json(['success' => false], 404);
        $card->delete();
        return response()->json(true);
    }
}
