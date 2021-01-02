example-jwt

Nesse exemplo, mostro como usar jwt(JSON WEB TOKEN) no Laravel de forma que possa ser aplicado à qualquer estrutura de tabela de usuário, por exemplo. Outra coisa que iremos ver é o uso de um Middleware nas API's para aplicarmos uma validação via jwt. Antes disso, só para explicar, JWT é um token auto-contido e baseado no padrão RFC7519 da W3C, formado por um header, um payload e um secret que são encriptados e enviados como uma string.

No primeiro passo, iremos instalar a do JWT no nosso projeto Laravel. Para isso, devemos acessar a pasta do projeto pelo cmd / bash e usar o seguinte comando:

composer require tymon/jwt-auth

Caso esteja usando uma versão do Laravel 5.4 ou inferior, devemos adicionar um "service provider" no array 'providers' localizado em config/app.php. Segue abaixo o código:

Tymon\JWTAuth\Providers\LaravelServiceProvider::class,

OBS:"No exemplo que criei, usei a versão 8.20.1 .

Agora devemos publicar o pacote através do comando:

php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"

Agora iremos utilizar o comando para gerar um hash para o JWT, esse hash será criado na constante "JWT_SECRET" do arquivo .env da sua aplicação:

php artisan jwt:secret

OBS:"Caso baixe o exemplo, lembre-se de criar o arquivo .env para poder utilizar o comando descrito acima".

Outro detalhe que fiz para que funcionasse, foi editar o arquivo config/jwt.php da chave 'required_claims' => ['iss', 'iat', 'exp', 'nbf', 'jti', 'sub'] eu removi a reinvidicação não utilizada (no meu caso, 'sub'). Importante frisar que no exemplo feito, mesmo que se faça login novamente e utilizar o token anterior, o Widdleware permitirá que acesse o controler, pois a função dele é apenas validar o token, e no caso, o anterior ainda é valido. por padrão o token tem uma validade de 1 hora.

Agora já podemos usar JWT no Laravel, abaixo irei prosseguir mostrando alguns passos de como criei a aplicação e de como usei um Middleware com validação jwt.

Passo 1 - Apaguei a classe Users e as migrations criadas por default no projeto, pois o intuito é criar tudo do zero.

Passo 2 - Criei o Model Usuarios com o seguinte comando: php artisan make:model Usuarios; utulizei o "SoftDeletes" para exclusão lógica e adicionei alguns campos como id, nome, CPF, email, senha e token.

Passo 3 - Criei um Migration para gerar a tabela usuarios com o seguinte comando: php artisan make:migration create_usuarios_table --create=usuarios; e lá informei sua chave id como bigIncrements, cpf e email como unique, timestamps e softDeletes com o campo deleted_at. Para mais detalhes, consultar o arquivo 2021_01_01_185744_create_usuarios_table em database/migrations.

Passo 4 - Usei uma biblioteca chamada para validar CPF e CNPJ, para adicionar o projeto, devemos acessar a pasta do projeto pelo cmd / bash e usar o seguinte comando: composer require laravellegends/pt-br-validator

Passo 5 - Criei um controller chamado UtilsController para criar funções que poderiam ser chamadas em lugares diferentes do projeto. Para criar o controller, devemos acessar a pasta do projeto pelo cmd / bash e usar o seguinte comando: php artisan make:controller Api/UtilsController

Passo 6 - No UtilsController criei uma função para validar CPF.

Passo 7 - Criei um Controller chamado UsuariosController e criei funções para cadastrar e excluir o usuário. Para criar o controller, devemos acessar a pasta do projeto pelo cmd / bash e usar o seguinte comando: php artisan make:controller Api/UsuariosController

Passo 8 - Criei um Controller chamado AuthenticateController para cuidar da parte de autenticação do usuário como login e alterar senha. Para criar o controller, devemos acessar a pasta do projeto pelo cmd / bash e usar o seguinte comando: php artisan make:controller Api/AuthenticateController; analisem o código para mais detalhes.

Passo 9 - Criei um Middleware chamado JwtAuthenticate para poder receber e validar o token que foi criado ao fazer o login. Para criar o Middleware, devemos acessar a pasta do projeto pelo cmd / bash e usar o seguinte comando: php artisan make:middleware JwtAuthenticate; Após criar o Middleware, devemos adiciona-lo no array "protected $routeMiddleware" no arquivo app/Http/Kernel.php. para isso, escreve o seguinte código: 'JwtAuthenticate' => \App\Http\Middleware\JwtAuthenticate::class,

Passo 10 - No Laravel 8 temos uma diferença para criar as rotas, devemos agora passar o caminho completo do controller, para usar como o de costume "passando apenas o nome do controle" devemos editar o arquivo app/Providers/RouteServiceProvider.php e em "protected $namespace" devemos adicionar o caminho dos controllers "protected $namespace = 'App\Http\Controllers'";

Passo 11 - Criei algumas rotas em routes/api.php e em algumas delas adicionei o Middleware JwtAuthenticate de forma que só terá acesso a essas rotas se o usuário estiver logado.

Passo 12 - Ao testar as rotas que tenham o Middleware, não esqueça de preencher corretamente o cabeçalho da requisição. Exemplo:

Content-Type: application/json

Authorization: bearer eyJ0eXAiOiJKV1QiLC.........(TOKEN)
