@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card">
                    <div class="card-header"><b>Proyecto: </b> {{ $project->name }}</div>

                    <div class="card-body py-4">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="notification" class="notification"></div>
@endsection


