@extends('components.layouts.app')
@section('content')
    <livewire:vouchers.voucher-verify :reference_no="$reference_no" />
@endsection