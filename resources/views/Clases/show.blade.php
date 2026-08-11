@extends('layouts.app')

@section('template_title')
    {{ $clase->name ?? 'Show Clase' }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm float-right" href="{{ route('clases.index') }}"> Volver</a>
                            @if (Auth::user()->nivel>=10)
                            <a href="{{ route('clases.create') }}" class="btn btn-success btn-sm float-right"  data-placement="left">
                                {{ __('Nueva Clase') }}
                            </a>
                            @endif  
                        </div>
                        
                        <div class="float-left">
                            <span class="card-title">Clases</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        @if ((((int)Auth::user()->nivel)>9))
                                            <th>id</th>
                                            <th>Orden</th>
                                            <th>Titulo</th>
                                            <th>Duracion</th>
                                            <th>Descripcion</th>
                                        @else
                                            <th>Descripcion</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($clases as $clase)
                                        @if ((((int)Auth::user()->nivel)>9))
                                            <tr>
                                                <td>{{ $clase->id_clase }}</td>
                                                <td>{{ $clase->orden }}</td>
                                                <td>{{ $clase->titulo }}</td>
                                                <td>{{ $clase->duracion }}</td>
                                                <td>{{ $clase->descripcion }}</td>
                                                <td>
                                                    <form action="{{ route('clases.destroy',$clase->id_clase) }}" method="POST">
                                                        <a class="btn btn-sm btn-success" href="{{ route('clases.edit',$clase->id_clase) }}"><i class="fa fa-fw fa-edit"></i> Edit</a>
                                                        <a class="btn btn-sm btn-danger" href="{{ route('clases.destroy',$clase->id_clase) }}"><i class="fa fa-fw fa-trash"></i> Delete</a>
                                                        
                                                    </form>
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td><h4>{{ $clase->titulo }} Duracion:{{ $clase->duracion }} </h4></td>
                                            </tr>                                            
                                            <tr>
                                                <td>{{ $clase->descripcion }}</td>
                                            </tr>                                            
                                        @endif
                                            <!--
                                                <td>
                                                <form action="{{ route('clases.destroy',$clase->fecha) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('clases.show',$clase->fecha) }}"><i class="fa fa-fw fa-eye"></i> Ver</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('clases.edit',$clase->fecha) }}"><i class="fa fa-fw fa-edit"></i> Asistire</a>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-fw fa-trash"></i> Delete</button>
                                                </form>
                                            </td>
                                            -->
                                        
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </section>
@endsection
