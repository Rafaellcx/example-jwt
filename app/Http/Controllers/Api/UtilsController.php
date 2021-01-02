<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LaravelLegends\PtBrValidator\Rules\FormatoCpf;

class UtilsController extends Controller
{
    public function validaCpf(Request $request){
        try{

            $dados = $request->validate([
                'cpf'  => ['required', new FormatoCpf]
                // outras validações aqui
            ]);

            $retorno['tipo'] =  'sucesso';
            $retorno['mensagem'] = 'ok';
            return json_encode($retorno);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $erro = $e->errors();
            $retorno['tipo'] =  'erro';
            $retorno['mensagem'] = $erro['cpf'][0];
            return json_encode($retorno);
        }
    }
}
