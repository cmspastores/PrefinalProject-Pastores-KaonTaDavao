@extends('layouts.app')

@section('title', ($eatery !== null ? 'Edit '.$eatery->name : 'Add an eatery').' | Davao Eats')

@section('content')
    @include('eateries._form', [
        'eatery' => $eatery,
        'districts' => $districts,
        'features' => $features,
    ])
@endsection
