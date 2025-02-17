<h2>
    {{$title}}
    <span class="d-block d-sm-inline float-sm-end">
    {{$slot}}
    <a href="{{ $urlBack ?? route('home')}}" class="btn btn-primary"><i class="fas fa-chevron-left"></i></a>
    </span>
</h2>
<hr>