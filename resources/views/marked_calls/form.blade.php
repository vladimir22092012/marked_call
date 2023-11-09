@extends('layout')

@section('title', 'Параметры разметки')


@section('content')
    <br>
    <main class="form-signin w-25 m-auto" id="marked_app">
        <form action="/start" method="POST" id="mainform">
            <h1 class="h3 mb-3 fw-normal">Разметка</h1>

            <div class="form-floating">
                <select class="form-control" id="owner" name="owner">
                    @foreach($owners as $key => $owner)
                        <option value="{{$key}}">{{$owner}}</option>
                    @endforeach
                </select>
                <label for="owner">Владелец</label>
            </div>
            <br>
            <div class="form-floating">
                <select class="form-control" name="type" id="type">
                    <option value="date">Все звонки за выбранный период</option>
                    <option value="call">Выбор по номеру</option>
                </select>
                <label for="owner">Выбор звонка</label>
            </div>
            <br>
            <div class="form-floating" style="display: none">
                <input type="text" id="call" name="call" class="form-control">
                <label for="call">ID Звонка</label>
            </div>
            <div class="form-floating">
                <input id="date" type="text" name="date" class="form-control">
                <label for="date">Период</label>
            </div>
            <br>
            <button class="btn btn-primary w-100 py-2" onclick="start" type="button">Разметить</button>
        </form>
    </main>
    <script>
        function start() {
            console.log($('#mainform').serialize())
        }
    </script>
@endsection

