<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuarios;

class UsuariosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Usuarios = Usuarios::select('id','cpf', 'nome', 'email')->get();
        
        if(count($Usuarios) > 0){
            return json_encode($Usuarios);
        }else{
            $retorno['tipo']     =  'erro';
            $retorno['mensagem'] =  'Nenhum registro encontrado.';
            return json_encode($retorno);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $UtilsController = new UtilsController();
        $validaCpf = $UtilsController->validaCpf($request);
        $json_ValidaCpf = json_decode($validaCpf);
        
        if($json_ValidaCpf->{'tipo'} == 'erro'){
            return $validaCpf;
        }

        $validaCampos = $this->validaCampos($request);

        if($validaCampos['tipo'] == 'erro'){
            return json_encode($validaCampos);
        }

        $Usuarios = Usuarios::where('cpf', '=', $request->cpf)
        ->whereNull('deleted_at')
        ->first();
        
        if(!empty($Usuarios)){
            $retorno['tipo']     =  'erro';
            $retorno['mensagem'] =  'Usuário já cadastrado.';
            return json_encode($retorno);
        }

        try {
            $Usuarios = new Usuarios();
            $Usuarios->cpf   = $request->cpf;
            $Usuarios->nome  = $request->nome;
            $Usuarios->email = $request->email;
            $Usuarios->senha = md5($request->senha);
            $Usuarios->save();

            $retorno['tipo']     = 'sucesso';
            $retorno['mensagem'] = 'Usuário cadastrado com sucesso.';
            return json_encode($retorno);
        } catch (\Throwable $th) {
            $retorno['tipo']     = 'erro';
            $retorno['mensagem'] = 'Ops, ocorreu algo de errado com a requisição, favor, entrar em contato com o suporte.';
            return json_encode($retorno);
        }

    }

    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        /*
          Como Criei CPF e EMAIL para serem únicos, verifico se os mesmos já existem
          para outro ID antes da atualizar para dar um retorno mais preciso do problema,
          nesse caso, a verificação é opcional.
        */
        $Usuarios = Usuarios::where('id', '<>', $request->id)
        ->where('cpf', '=', $request->cpf)
        ->orWhere('email', $request->email)
        ->first();

        if(empty($Usuarios)){
            $retorno['tipo'] = 'erro';
            $retorno['mensagem'] = 'CPF e/ou e-mail já existe(m) para outro usuário.';
            return json_encode($retorno);
        }

        try {
            $UpdateUsuarios = Usuarios::find($request->id)
            ->update(['cpf' => $request->cpf, 'nome' => $request->nome, 'email' => $request->email]);
            
            $retorno['tipo']     = 'sucesso';
            $retorno['mensagem'] = 'Usuário atualizado com Sucesso.';
            return json_encode($retorno);
        } catch (\Throwable $th) {
            $retorno['tipo'] = 'erro';
            $retorno['mensagem'] = 'Ops, ocorreu um erro ao tentar atualizar o usuário.';
            return json_encode($retorno);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(!$request->cpf){
            $retorno['tipo'] = 'erro';
            $retorno['mensagem'] = 'cpf não informado.';
            return json_encode($retorno);
        }

        $Usuarios = Usuarios::where('cpf', '=', $request->cpf)->first();

        if(empty($Usuarios)){
            $retorno['tipo'] = 'erro';
            $retorno['mensagem'] = 'usuário não encontrado.';
            return json_encode($retorno);
        }

        try {
            $Usuarios = Usuarios::where('cpf', '=', $request->cpf)->delete();
        } catch (\Throwable $th) {
            $retorno['tipo'] =  'erro';
            $retorno['mensagem'] = 'Ops, ocorreu um erro ao tentar excluir o usuário.';
            return json_encode($retorno);
        }

        $retorno['tipo']     = 'sucesso';
        $retorno['mensagem'] = 'Usuário excluído com Sucesso.';
        return json_encode($retorno);
    }

    public function validaCampos(Request $request){
       
        try{

            $mensagens = [
                'required' => ':attribute é obrigatório.',
                'email.email' => 'Digite um email válido.',
                'min' => 'É necessário no mínimo :min caracteres no :attribute.',
                'max' => ':attribute não pode ser maior que :max.',
            ];
        
            $request->validate([
                'nome'  => 'required|string|min:3|max:255',
                'cpf'   => 'required|string|min:11|max:14',
                'email' => 'required|string|max:200|email',
                'senha' => 'required|string|max:50|min:6',
            ], $mensagens);
            
            $retorno['tipo'] = 'sucesso';
            $retorno['mensagem'] = 'ok';
            return $retorno;
        } catch (\Illuminate\Validation\ValidationException $e) {

            $mensagens = json_decode($e->validator->messages(), true);

            $retorno['tipo'] = 'erro';
            $retorno['mensagem'] = array();
            
            $campos = ['nome', 'cpf', 'email', 'senha'];

            for ($i=0; $i < count($campos) ; $i++) { 

                if(isset($mensagens[$campos[$i]])){

                    foreach ($mensagens[$campos[$i]] as $key => $value) {
                        array_push($retorno['mensagem'], $value);
                    }
                }
            }
            
            return $retorno;
        }
    }
}
