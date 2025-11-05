@extends('errors::layout')

@section('title', 'Hozzáférés megtagadva')
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Nincs jogosultsága ennek az oldalnak a megtekintéséhez.'))
