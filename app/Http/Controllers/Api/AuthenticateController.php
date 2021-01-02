<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Usuarios;

use JWTFactory;
use JWTAuth;


class AuthenticateController extends Controller
{
    public function login(){
        if(request(['senha'])) {
            $passwordRequest = request(['senha']);
            $passwordRequest = $passwordRequest['senha'];
            $passwordRequest = md5($passwordRequest);
        }else{
            $retorno['tipo']     = 'erro';
            $retorno['mensagem'] = 'Campo "senha" não informada.';
            return json_encode($retorno);
        }

        if(request(['cpf'])) {
            $cpf = request(['cpf']);
            $cpf = $cpf['cpf'];
        }else{
            $retorno['tipo']     = 'erro';
            $retorno['mensagem'] = 'Campo "cpf" não informado.';
            return json_encode($retorno);
        }
  
        if($cpf != null){
            $Usuarios = Usuarios::where('cpf','=', $cpf)
            ->whereNull('deleted_at')
            ->get();

            if(!empty($Usuarios)){
                $senha = '';
                foreach ($Usuarios as $key => $value) {
                    $senha = $value['senha'];
                    unset($value['senha']);
                }

                if($passwordRequest == $senha){
                    $payload = JWTFactory::emptyClaims()->addClaims([
                        'sub' => request(['cpf'])
                    ])->make();

                    $token = JWTAuth::encode($payload);
                
                    $UpdateUsuario = Usuarios::where('cpf','=', $cpf)
                    ->whereNull('deleted_at')
                    ->update(['token' => (string)$token]);
                    
                    $retorno['cpf']   = $cpf;
                    $retorno['token'] = (string)$token;

                    return json_encode($retorno);

                }else{
                    $retorno['tipo']     = 'erro';
                    $retorno['mensagem'] = 'Senha inválida.';
                    return json_encode($retorno);
                }

            }else{
                $retorno['tipo']     = 'erro';
                $retorno['mensagem'] = 'CPF não encontrado.';
                return json_encode($retorno);
            }
        }else{
            $retorno['tipo']     = 'erro';
            $retorno['mensagem'] = 'CPF não informado.';
            return json_encode($retorno);
        }
    }

    public function alterarSenha(Request $request){
       
        $validaCampos = $this->validaCampos($request);

        if($validaCampos['tipo'] == 'erro'){
            return json_encode($validaCampos);
        }

        $passwordRequest = $request->senha;
        $passwordRequest = md5($passwordRequest);
   
        $Usuarios = Usuarios::where('cpf','=', $request->cpf)
        ->whereNull('deleted_at')
        ->get();

        if(!empty($Usuarios)){

            $senha = '';
            foreach ($Usuarios as $key => $value) {
                $senha = $value['senha'];
                unset($value['senha']);
            }

            if($passwordRequest == $senha){
                $senhaNova = md5($request->senhanova);

                $UpdateUsuarios = Usuarios::where('cpf','=', $request->cpf)
                ->update(['senha' => $senhaNova]);

                $retorno['tipo']     = 'sucesso';
                $retorno['mensagem'] = 'Senha alterada com sucesso.';
                return json_encode($retorno);
            }else{
                $retorno['tipo']     = 'erro';
                $retorno['mensagem'] = 'Senha inválida.';
                return json_encode($retorno);
            }
        }else{
            $retorno['tipo']     = 'erro';
            $retorno['mensagem'] = 'CPF não encontrado.';
            return json_encode($retorno);
        }
       
        
    }

    public function validaCampos(Request $request){
        try{

            $mensagens = [
                'required' => ':attribute é obrigatório.',
                'min' => 'É necessário no mínimo :min caracteres no :attribute.',
                'max' => ':attribute não pode ser maior que :max.',
            ];
        
            $request->validate([
                'cpf'   => 'required',
                'senha' => 'required|string|max:50|min:6',
                'senhanova' => 'required|string|max:50|min:6',
            ], $mensagens);
            
            $retorno['tipo'] = 'sucesso';
            $retorno['mensagem'] = 'ok';
            return $retorno;
        } catch (\Illuminate\Validation\ValidationException $e) {

            $mensagens = json_decode($e->validator->messages(), true);

            $retorno['tipo'] = 'erro';
            $retorno['mensagem'] = array();
            
            $campos = ['cpf', 'senha', 'senhanova'];

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
