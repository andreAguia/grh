<?php
/**
 * Cadastro de Servidores
 *  
 * By Alat
 */

# Reservado para a matrícula do servidor logado
$matricula = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($matricula,13);

if($acesso){    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','incluir');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    $parametroNomeMat = retiraAspas(post('parametroNomeMat'));
    $parametroCargo = post('parametroCargo','*');
    $parametroCargoComissao = post('parametroCargoComissao','*');
    $parametroLotacao = post('parametroLotacao','*');
    $parametroPerfil = post('parametroPerfil','*');
    $parametroSituacao = post('parametroSituacao','1');

    # Ordem da tabela
    $orderCampo = get('orderCampo');
    $orderTipo = get('orderTipo');

    # Insere jscript extra da inclusão de servidor que oculta campos de acordo com o perfil
    $jscript = "<script language='JavaScript' >

                function exibeEscondeCampos()
                {                
                    switch(document.novoServidor.perfil.value)
                    {
                        case '1':
                            document.novoServidor.cargo.disabled = false;
                            abreDivId('divEstatutarios');
                            fechaDivId('divCedidos');
                            fechaDivId('divConvidados');
                            fechaDivId('divEstagiarios');                        
                            break;
                        case '2':
                            document.novoServidor.cargo.disabled = true;
                            abreDivId('divCedidos');
                            fechaDivId('divEstatutarios');
                            fechaDivId('divConvidados');
                            fechaDivId('divEstagiarios'); 
                            break;
                        case '3':
                            document.novoServidor.cargo.disabled = true;
                            abreDivId('divConvidados');
                            fechaDivId('divCedidos');
                            fechaDivId('divEstatutarios');
                            fechaDivId('divEstagiarios'); 
                            break;
                        case '4':
                            document.novoServidor.cargo.disabled = true;
                            abreDivId('divEstagiarios');
                            fechaDivId('divCedidos');
                            fechaDivId('divConvidados');
                            fechaDivId('divEstatutarios'); 
                            break;
                        default:
                            document.novoServidor.cargo.disabled = true;
                            document.novoServidor.salario.disabled = true;
                            fechaDivId('divCedidos');
                            fechaDivId('divConvidados');
                            fechaDivId('divEstatutarios');
                            fechaDivId('divEstagiarios');
                            break;
                    }
                }
                </script>";

    # Começa uma nova página
    $page = new Page();
    $page->set_jscript($jscript);
    $page->set_bodyOnLoad('exibeEscondeCampos();');
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

####################################################################################
    switch ($fase)
    {
        # Inclusão de Novo Servidor    
        case "incluir" :
            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Botão voltar
            botaoVoltar('servidor.php');

            # Título
            titulo('Incluir Novo Servidor');
            $callout = new Callout();
            $callout->abre();
            
            # Exibe o nº da página
            $div = new Div("right");
            $div->abre();
            badge("1","warning");
            $div->fecha();
            
            # Inicia o formulário
            $form = new Form('?fase=validaCPF','novoServidor');

            # CPF
            $controle = new Input('cpf','cpf','CPF:',1);
            $controle->set_size(20);            
            $controle->set_linha(1);
            $controle->set_col(6);
            $controle->set_required(TRUE);
            $controle->set_autofocus(true);
            $controle->set_title('O CPF do Novo Servidor');
            $form->add_item($controle);

            # submit
            $controle = new Input('submit','submit');
            $controle->set_valor(' Incluir ');
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_accessKey('E');
            $form->add_item($controle);

            $form->show();
            br(2);
            
            # Mensagem
            callout('Para inclusão de um novo servidor é necessário que se informe o CPF para que o sistema verifique se o mesmo já está cadastrado em uma matrícula anterior.');
            
            $callout->fecha();
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

####################################################################################

        # Valida o CPF e verifica se tem servidor cadastrado com esse CPF    
        case "validaCPF" :

            # Variáveis para tratamento de erros
            $erro = 0;	  	// flag de erro: 1 - tem erro; 0 - não tem	
            $msgErro = null; 	// repositório de mensagens de erro        

            # Pega os valores digitados
            $cpf = post('cpf');        

            # Instancia um objeto de validação
            $valida = new Valida();

            # Verifica se o CPF foi digitado
            if ($valida->vazio($cpf)){
                $msgErro.='Você tem que digitar o CPF!<br/>';
                $erro = 1;
            }

            # Verifica validade do CPF
            if (!$valida->cpf($cpf)){
                $msgErro.='CPF inválido!<br/>';
                $erro = 1;
            }

            # Verifica se o CPF já está cadastrado
            $idPessoa = $pessoal->get_idpessoaCPF($cpf);

            if(!is_null($idPessoa)){
                # Servidor ativo
                if(!is_null($pessoal->get_idPessoaAtiva($idPessoa))){
                    $msgErro.='Funcionário com matrícula ativa! Não pode ser incluído em outra matrícula!<br/>';
                    $erro = 1;
                }
            }

            # Verifia se houve erro 
            if ($erro == 1){
                br(2);
                # Limita o tamanho da tela
                $grid = new Grid();
                $grid->abreColuna(3);

                $grid->fechaColuna();
                $grid->abreColuna(6);

                # painel usando o callout
                $painel2 = new Callout();
                $painel2->set_botaoOk(NULL,"history.go(-1)");
                $painel2->abre();
                    p($msgErro);
                $painel2->fecha();

                $grid->fechaColuna();
                $grid->abreColuna(3);

                $grid->fechaColuna();
                $grid->fechaGrid();
            }else{
                set_session('sessionCpf',$cpf);
                loadPage('?fase=incluir2');
            }
            break;

####################################################################################

        # Continua o cadastro do servidor 
        case "incluir2" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Pega o CPF da session
            $cpf = get_session('sessionCpf');

            # Variaveis de quando o servidor já for cadastrado
            $nome = null;
            $pis = null;

            # Verifica se o CPF já está cadastrado
            $idPessoa = $pessoal->get_idpessoaCPF($cpf);

            # pega o nome e o pis da pessoa (caso ja esteja cadastrado)
            if(!is_null($idPessoa)){
                $nome = $pessoal->get_nomeidPessoa($idPessoa);
                $pis = $pessoal->get_Pis($idPessoa);
            }

            # Botão voltar
            botaoVoltar('?fase=incluir');

            # Título
            titulo('Incluir Novo Servidor');
            $callout = new Callout();
            $callout->abre();
            
            # Exibe o nº da página
            $div = new Div("right");
            $div->abre();
            badge("2","warning");
            $div->fecha();

            # Mensagem
            if(is_null($nome))
                $mensagem = 'O CPF '.$cpf.' não está cadastrado no sistema.';
            else
                $mensagem = 'O CPF '.$cpf.' já está cadastrado para o servidor INATIVO: '.$nome.'. Entre com os dados da sua nova matrícula.';
            
            # Mensagem
            br();
            callout($mensagem,'warning');
            
            $form = new Form('?fase=validaDados','novoServidor');
            
                # CPF
                $controle = new Input('cpf','cpf','CPF:',1);
                $controle->set_size(20);            
                $controle->set_linha(1);
                $controle->set_readonly(true);
                $controle->set_valor($cpf);
                $controle->set_required(TRUE);
                $controle->set_col(3);
                $controle->set_title('O CPF do Novo Servidor');
                $form->add_item($controle);            

                # Nome
                $controle = new Input('nome','texto','Nome:',1);
                $controle->set_size(50);
                $controle->set_col(6);
                $controle->set_linha(1);
                $controle->set_required(TRUE);
                if(!is_null($nome)){
                    $controle->set_valor($nome);
                    $controle->set_readonly(true);                    
                }else{
                    $controle->set_autofocus(true);
                }
                
                $controle->set_title('O nome do servidor.');
                $form->add_item($controle);

                # Perfil                
                $perfil = $pessoal->select('SELECT idperfil,
                                                   nome
                                              FROM tbperfil
                                          ORDER BY nome');

                array_push($perfil, array(null,null)); 

                $controle = new Input('perfil','combo','Perfil:',1);
                $controle->set_size(20);            
                $controle->set_linha(1);
                $controle->set_required(TRUE);
                $controle->set_col(3);
                $controle->set_title('O perfil do Servidor.');
                $controle->set_array($perfil);
                $controle->set_onChange('exibeEscondeCampos();');
                $form->add_item($controle);

                # Mensagem sobre a matrícula
                #$mensagem = 'Você tem a opção de digitar a matrícula ou deixar em branco para que o sistema gere automaticamente.    Se uma matrícula for informada ela deve seguir os limítes definidos no cadastro de perfil.';
                #p($mensagem);
                #$p->show();
                #$form->add_item($p);

                # Matrícula
                $controle = new Input('matricula','texto','Matrícula: (sem o dígito verificador)',1);
                $controle->set_size(20);            
                $controle->set_linha(2);
                $controle->set_col(3);                
                $controle->set_title('A matrícula do servidor.');
                $form->add_item($controle);

                 # Lotação               
                $lotacao = $pessoal->select('SELECT idlotacao, 
                                   concat(UADM,"-",DIR,"-",GER) as lotacao
                              FROM tblotacao
                             WHERE ativo = "Sim"
                          ORDER BY lotacao');

                array_push($lotacao, array(null,null)); # Adiciona o valor de nulo

                $controle = new Input('lotacao','combo','Lotação Inicial:',1);
                $controle->set_size(20);            
                $controle->set_linha(2);
                $controle->set_col(6);
                $controle->set_required(TRUE);                
                $controle->set_title('A Loteção do Servidor.');
                $controle->set_array($lotacao);
                $form->add_item($controle);

                # Data de Admissão
                $controle = new Input('dtAdmissao','date','Data de Admissão:',1);
                $controle->set_size(15);
                $controle->set_col(3);
                $controle->set_required(TRUE);
                $controle->set_linha(2);
                $controle->set_title('A data de admissão do servidor.');
                $form->add_item($controle);

                # PIS/Pasep
                if(is_null($pis)){                
                    $controle = new Input('pisPasep','texto','Pis/Pasep:',1);
                    $controle->set_size(12);            
                    $controle->set_linha(3);
                    $controle->set_col(3);
                    $controle->set_required(TRUE);
                    $controle->set_title('O PIS/Pasep do servidor.');
                    $form->add_item($controle);
                }

                # Cargo                
                $cargo = $pessoal->select('SELECT idcargo,
                                   nome
                              FROM tbcargo
                          ORDER BY nome');

                array_push($cargo, array(null,null)); 

                $controle = new Input('cargo','combo','Cargo:',1);
                $controle->set_size(20);            
                $controle->set_linha(3);                
                $controle->set_title('O Cargo do Servidor.');
                $controle->set_col(6);
                $controle->set_array($cargo);
                $form->add_item($controle);      

                # submit
                $controle = new Input('submit','submit');
                $controle->set_valor(' Concluir ');
                $controle->set_size(20);
                $controle->set_formLinha(1);
                $controle->set_formAlign('center');
                $controle->set_accessKey('E');
                $form->add_item($controle);

            $form->show();		

            $callout->fecha(); 
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

####################################################################################

            # Valida os outros dados    
            case "validaDados" :

                # Variáveis para tratamento de erros
                $erro = 0;	  	// flag de erro: 1 - tem erro; 0 - não tem	
                $msgErro = null; 	// repositório de mensagens de erro

                # Pega os valores digitados
                $cpf = post('cpf');     
                $nome = post('nome'); 
                $perfil = post('perfil'); 
                $matricula = post('matricula'); 
                $lotacao = post('lotacao');
                $dtAdmissao = post('dtAdmissao');
                $pisPasep = post('pisPasep');
                $cargo = post('cargo'); 
                $classe = null;
                $idPessoa = $pessoal->get_idpessoaCPF($cpf);

                # Instancia um objeto de validação
                $valida = new Valida();

                # Verifica se o Nome foi digitado
                if ($valida->vazio($nome)){
                    $msgErro.='Você tem que informar o Nome do Servidor!<br/>';
                    $erro = 1;
                }

                # Verifica se o Perfil foi digitado
                if ($valida->vazio($perfil)){
                    $msgErro.='Você tem que informar o Perfil do Servidor!<br/>';
                    $erro = 1;
                }else{    # verificações da matrícula             
                    if((is_null($matricula)) OR ($matricula == "")){
                        # Gera uma nova matrícula
                        $matricula = $pessoal->get_novaMatricula($perfil);                
                    }

                    # Verifica se a matrícula já existe
                    if($pessoal->get_existeMatricula($matricula)){
                        $msgErro.='Essa matrícula já está em uso!<br/>';
                        $erro = 1;
                    }
                }

                # Verifica se a lotação foi digitada
                if ($valida->vazio($lotacao)){
                    $msgErro.='Você tem que informar a Lotação do Servidor!<br/>';
                    $erro = 1;
                }

                # Verifica se a Admissão foi digitada
                if ($valida->vazio($dtAdmissao)){
                    $msgErro.='Você tem que informar a Data de Admissão do Servidor!<br/>';
                    $erro = 1;
                }

                # Verifica o Pis              
                if(is_null($idPessoa)){ // Verifica se a pessoa está cadastrada
                    # Verifica se o Pis foi digitado 
                    if ($valida->vazio($pisPasep)){
                        $msgErro.='Você tem que informar o Pis/Pasep do Servidor!<br/>';
                        $erro = 1;
                    }

                    # Verifica se o pis já existe
                    $idPessoaDuplicataPis = $pessoal->get_idpessoaPis($pisPasep);                
                    if(!is_null($idPessoaDuplicataPis)){
                        $msgErro.='Esse Pis/Pasep já está cadastrado para o servidor: '.$pessoal->get_nomeidPessoa($idPessoaDuplicataPis).'!<br/>';
                        $erro = 1;
                    }

                    # Verifica validade do pis (ainda não encontrei funçao que funcione)
                    #if ($valida->pis($pisPasep)) 
                    #{
                    #    $msgErro.='O número do Pis não é válido !!\n';
                    #    $erro = 1;
                    #}
                }

                # Verifica se o Cargo foi digitado
                if (($perfil == 1) AND ($valida->vazio($cargo))){
                    $msgErro.='Você tem que informar o Cargo do Servidor!<br/>';
                    $erro = 1;
                }
                
                # formata data quando vier de um controle html5 (vem yyyy/mm/dd)
                if(HTML5){
                    $dtAdmissao = date_to_php($dtAdmissao);
                }
                
                # verifica a validade da data de admissao
                if (!validaData($dtAdmissao)){
                    $msgErro.='A data de admissão não é válida!<br/>';
                    $erro = 1;
                }else{
                    $dtAdmissao = date_to_bd($dtAdmissao);
                }

                # Verifia se houve erro 
                if ($erro == 1){
                    br(2);
                    # Limita o tamanho da tela
                    $grid = new Grid();
                    $grid->abreColuna(3);

                    $grid->fechaColuna();
                    $grid->abreColuna(6);

                    # painel usando o callout
                    $painel2 = new Callout();
                    $painel2->set_botaoOk(NULL,"history.go(-1)");
                    $painel2->abre();
                        p($msgErro);
                    $painel2->fecha();

                    $grid->fechaColuna();
                    $grid->abreColuna(3);

                    $grid->fechaColuna();
                    $grid->fechaGrid();
                }else{
                    # Grava os dados

                    # Tabelas tbpessoa e tbdocumentacao
                    if(is_null($idPessoa)) // Verifica se a pessoa está/ou não cadastrada
                    {
                        # Gravar na tbpessoa
                        # dados
                        $campos = array('nome');
                        $valor = array($nome);
                        $idValor = null;
                        $tabela = 'tbpessoa';

                        # gravação
                        $pessoal->gravar($campos,$valor,$idValor,$tabela,null,false);

                        # pega o id
                        $idPessoa = $pessoal->get_lastId();

                        ###################################

                        # Grava na tbdocumentacao
                        # dados
                        $campos = array('CPF','pisPasep','idPessoa');
                        $valor = array($cpf,$pisPasep,$idPessoa);
                        $idValor = null;
                        $tabela = 'tbdocumentacao';

                        # gravação
                        $pessoal->gravar($campos,$valor,$idValor,$tabela,null,false);
                    }

                    ###################################

                    # Grava na tbfuncionario
                    # dados
                    $campos = array('matricula','idPerfil','idPessoa','idCargo','dtAdmissao','Sit');
                    $valor = array($matricula,$perfil,$idPessoa,$cargo,$dtAdmissao,1);
                    $idValor = null;
                    $tabela = 'tbfuncionario';

                    # gravação
                    $pessoal->gravar($campos,$valor,$idValor,$tabela,null,false);

                    ###################################

                    # Grava na tbhistlot
                    # dados
                    $campos = array('matricula','lotacao','data','motivo');
                    $valor = array($matricula,$lotacao,$dtAdmissao,'Lotação Inicial');
                    $idValor = null;
                    $tabela = 'tbhistlot';

                    # gravação
                    $pessoal->gravar($campos,$valor,$idValor,$tabela,null,false);

                    ###################################

                    # Grava na tbprogressao
                    if($perfil == 1) // somente estatutários
                    {
                        # dados
                        $nivel = $pessoal->get_nivelCargoCargo($cargo); // nível do cargo
                        $plano = $pessoal->get_idPlanoAtual();      // plano de carcos atual
                        $classe = $pessoal->get_classeInicial($plano,$nivel);  // primeiro salário desse nível                

                        $campos = array('matricula','idTpProgressao','dtInicial','idClasse');
                        $valor = array($matricula,1,$dtAdmissao,$classe);
                        $idValor = null;
                        $tabela = 'tbprogressao';

                        # gravação
                        $pessoal->gravar($campos,$valor,$idValor,$tabela);
                    }
                    
                    ###################################

                    # Grava na tbCedido
                    if($perfil == 2) // somente cedidos
                    {
                        $campos = array('matricula');
                        $valor = array($matricula);
                        $idValor = null;
                        $tabela = 'tbcedido';

                        # gravação
                        $pessoal->gravar($campos,$valor,$idValor,$tabela);
                    }
                    ###################################

                    # Carrega a página do servidor criado
                    set_session('matriculaGrh',$matricula);
                    loadPage('servidorMenu.php');
                }  
                break;

    }									 	 		

    $page->terminaPagina();
}
?>