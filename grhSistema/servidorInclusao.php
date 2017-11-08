<?php
/**
 * Cadastro de Servidores
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','incluir');

    # Insere jscript extra da inclusão de servidor que oculta campos de acordo com o perfil
    $jscript = "<script language='JavaScript' >

                function exibeEscondeCampos()
                {                
                    switch(document.novoServidor.perfil.value)
                    {
                        case '1':
                            document.novoServidor.cargo.disabled = FALSE;
                            abreDivId('divEstatutarios');
                            fechaDivId('divCedidos');
                            fechaDivId('divConvidados');
                            fechaDivId('divEstagiarios');                        
                            break;
                        case '2':
                            document.novoServidor.cargo.disabled = TRUE;
                            abreDivId('divCedidos');
                            fechaDivId('divEstatutarios');
                            fechaDivId('divConvidados');
                            fechaDivId('divEstagiarios'); 
                            break;
                        case '3':
                            document.novoServidor.cargo.disabled = TRUE;
                            abreDivId('divConvidados');
                            fechaDivId('divCedidos');
                            fechaDivId('divEstatutarios');
                            fechaDivId('divEstagiarios'); 
                            break;
                        case '4':
                            document.novoServidor.cargo.disabled = TRUE;
                            abreDivId('divEstagiarios');
                            fechaDivId('divCedidos');
                            fechaDivId('divConvidados');
                            fechaDivId('divEstatutarios'); 
                            break;
                        default:
                            document.novoServidor.cargo.disabled = TRUE;
                            document.novoServidor.salario.disabled = TRUE;
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
            $controle->set_col(4);
            #$controle->set_required(TRUE);
            $controle->set_autofocus(TRUE);
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
            $msgErro = NULL; 	// repositório de mensagens de erro        

            # Pega os valores digitados
            $cpf = post('cpf');

            # Verifica se o CPF foi digitado
            if (empty($cpf)){
                $msgErro.='Você tem que digitar o CPF!\n';
                $erro = 1;
            }

            # Verifica validade do CPF
            if (!validaCpf($cpf)){
                $msgErro.='CPF inválido!\n';
                $erro = 1;
            }

            # Verifica se o CPF já está cadastrado
            $idPessoa = $pessoal->get_idPessoaCPF($cpf);

            if(!is_null($idPessoa)){
                # Servidor ativo
                if(!is_null($pessoal->get_idPessoaAtiva($idPessoa))){
                    $msgErro.='Funcionário com matrícula ativa! Não pode ser incluído em outra matrícula!\n';
                    $erro = 1;
                }
            }

            # Verifia se houve erro 
            if ($erro == 1){
                alert($msgErro);
                back(1);
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
            $nome = NULL;
            $pis = NULL;

            # Verifica se o CPF já está cadastrado
            $idPessoa = $pessoal->get_idPessoaCPF($cpf);

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
            if (is_null($nome)) {
                $mensagem = 'O CPF ' . $cpf . ' não está cadastrado no sistema.';
            } else {
                $mensagem = 'O CPF ' . $cpf . ' já está cadastrado para o servidor INATIVO: ' . $nome . '. Entre com os dados da sua nova matrícula.';
            }

            # Mensagem do cpf
            br();
            callout($mensagem);
            
            # Mensagem sobre a matrícula
            $mensagem = 'Você tem a opção de digitar a matrícula ou deixar em branco para que o sistema gere automaticamente.<br/>Se uma matrícula for informada ela deve seguir os limítes definidos no cadastro de perfil.';
            callout($mensagem);
            
            $form = new Form('?fase=validaDados','novoServidor');
            
                # CPF
                $controle = new Input('cpf','cpf','CPF:',1);
                $controle->set_size(20);            
                $controle->set_linha(1);
                $controle->set_readonly(TRUE);
                $controle->set_valor($cpf);
                $controle->set_required(TRUE);
                $controle->set_col(2);
                $controle->set_title('O CPF do Novo Servidor');
                $form->add_item($controle);            

                # Nome
                $controle = new Input('nome','texto','Nome:',1);
                $controle->set_size(50);
                $controle->set_col(5);
                $controle->set_linha(1);
                $controle->set_required(TRUE);
                if(!is_null($nome)){
                    $controle->set_valor($nome);
                    $controle->set_readonly(TRUE);                    
                }else{
                    $controle->set_autofocus(TRUE);
                }                
                $controle->set_title('O nome do servidor.');
                $form->add_item($controle);
                                
                # Sexo
                $controle = new Input('sexo','combo','Sexo:',1);
                $controle->set_size(15);
                $controle->set_col(2);
                $controle->set_array(array(NULL,"Masculino","Feminino"));
                $controle->set_required(TRUE);
                $controle->set_linha(1);
                $controle->set_title('Sexo do Servidor.');
                $form->add_item($controle);
                
                # Data de Nascimento
                $controle = new Input('dtNasc','date','Data de Nascimento:',1);
                $controle->set_size(15);
                $controle->set_col(3);
                $controle->set_required(TRUE);
                $controle->set_linha(1);
                $controle->set_title('A data de nascimento do servidor.');
                $form->add_item($controle);

                # Perfil                
                $perfil = $pessoal->select('SELECT idperfil,
                                                   nome
                                              FROM tbperfil     
                                             WHERE novoServidor
                                          ORDER BY nome');

                array_push($perfil, array(NULL,NULL)); 

                $controle = new Input('perfil','combo','Perfil:',1);
                $controle->set_size(20);            
                $controle->set_linha(2);
                $controle->set_required(TRUE);
                $controle->set_col(3);
                $controle->set_title('O perfil do Servidor.');
                $controle->set_array($perfil);
                $controle->set_onChange('exibeEscondeCampos();');
                $form->add_item($controle);

                
                #$p->show();
                #$form->add_item($p);

                # Lotação               
                $lotacao = $pessoal->select('SELECT idlotacao, 
                                                    concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) as lotacao
                                               FROM tblotacao
                                              WHERE ativo
                                           ORDER BY tblotacao.DIR,tblotacao.GER');

                array_push($lotacao, array(NULL,NULL)); # Adiciona o valor de nulo

                $controle = new Input('lotacao','combo','Lotação Inicial:',1);
                $controle->set_size(20);            
                $controle->set_linha(2);
                $controle->set_col(6);
                $controle->set_required(TRUE);                
                $controle->set_title('A Lotação do Servidor.');
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
                    $controle->set_size(20);            
                    $controle->set_linha(3);
                    $controle->set_col(3);
                    $controle->set_required(TRUE);
                    $controle->set_title('O PIS/Pasep do servidor.');
                    $form->add_item($controle);
                }

                # Cargo                
                $cargo = $pessoal->select('SELECT idcargo, CONCAT(tbTipoCargo.cargo," - ",tbcargo.nome)'
                        . '                  FROM tbcargo JOIN tbTipoCargo USING (idTipoCargo)'
                        . '              ORDER BY tbTipoCargo.cargo,tbcargo.nome');

                array_push($cargo, array(NULL,NULL)); 

                $controle = new Input('cargo','combo','Cargo:',1);
                $controle->set_size(20);            
                $controle->set_linha(3);                
                $controle->set_title('O Cargo do Servidor.');
                $controle->set_col(6);
                $controle->set_array($cargo);
                $form->add_item($controle); 
                
                # IdFuncional
                $controle = new Input('idFuncional','texto','IdFuncional:',1);
                $controle->set_size(20);            
                $controle->set_linha(3);
                $controle->set_col(3);                
                $controle->set_title('A IdFuncional do servidor.');
                $form->add_item($controle);
                
                # Matrícula
                $controle = new Input('matricula','texto','Matrícula: (sem o dígito verificador)',1);
                $controle->set_size(20);            
                $controle->set_linha(3);
                $controle->set_col(3);                
                $controle->set_title('A matrícula do servidor.');
                $form->add_item($controle);

                # submit
                $controle = new Input('submit','submit');
                $controle->set_valor(' Concluir ');
                $controle->set_size(20);
                $controle->set_linha(1);
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
                $msgErro = NULL; 	// repositório de mensagens de erro

                # Pega os valores digitados
                $cpf = post('cpf');
                $sexo = post('sexo');
                $nome = post('nome'); 
                $perfil = post('perfil'); 
                $matricula = post('matricula'); 
                $dtNasc = post('dtNasc');
                $idFuncional = post('idFuncional'); 
                $lotacao = post('lotacao');
                $dtAdmissao = post('dtAdmissao');
                $pisPasep = post('pisPasep');
                $cargo = post('cargo'); 
                $classe = NULL;
                $idPessoa = $pessoal->get_idPessoaCPF($cpf);
                
                # Verifica se o Nome foi digitado
                if(empty($nome)){
                    $msgErro.='Você tem que informar o Nome do Servidor!\n';
                    $erro = 1;
                }
                
                # Verifica se o Sexo foi digitado
                if(empty($sexo)){
                    $msgErro.='Você tem que informar o Sexo do Servidor!\n';
                    $erro = 1;
                }

                # Verifica se o Perfil foi digitado
                if(empty($perfil)){
                    $msgErro.='Você tem que informar o Perfil do Servidor!\n';
                    $erro = 1;
                }else{
                    # Verifica se o Perfil permite novo servidor
                    if(!$pessoal->podeNovoServidor($perfil)){
                        $msgErro.='Esse perfil não permite novo servidor!\n';
                        $erro = 1;
                    }
                }
               
                # Verifica se a matrícula já existe
                if(!empty($matricula)){
                    if($pessoal->get_existeMatricula($matricula)){
                        $msgErro.='Essa matrícula já está em uso!\n';
                        $erro = 1;
                    }
                }else{
                    if(!empty($perfil)){
                        $matricula = $pessoal->get_novaMatricula($perfil);
                    }
                }               
                
                # Verifica se a lotação foi digitada
                if(empty($lotacao)){
                    $msgErro.='Você tem que informar a Lotação do Servidor!\n';
                    $erro = 1;
                }

                # Verifica se a Admissão foi digitada
                if(empty($dtAdmissao)){
                    $msgErro.='Você tem que informar a Data de Admissão do Servidor!\n';
                    $erro = 1;
                }
                
                # Verifica se a data de nascimento foi digitada
                if(empty($dtNasc)){
                    $msgErro.='Você tem que informar a Data de Nascimento do Servidor!\n';
                    $erro = 1;
                }
                
                # Verifica se servidor tem mais de 18 anos
                if(idade(date_to_php($dtNasc)) < 18){
                    $msgErro.='Você não pode cadastrar servidor menor de 18 anos.!\n';
                    $erro = 1;
                }

                # Verifica o Pis              
                if(is_null($idPessoa)){ // Verifica se a pessoa está cadastrada
                    # Verifica se o Pis foi digitado 
                    if (vazio($pisPasep)){
                        $msgErro.='Você tem que informar o Pis/Pasep do Servidor!\n';
                        $erro = 1;
                    }

                    # Verifica se o pis já existe
                    $idPessoaDuplicataPis = $pessoal->get_idPessoaPis($pisPasep);                
                    if(!is_null($idPessoaDuplicataPis)){
                        $msgErro.='Esse Pis/Pasep já está cadastrado para o servidor: '.$pessoal->get_nomeidPessoa($idPessoaDuplicataPis).'!\n';
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
                if (($perfil == 1) AND (empty($cargo))){
                    $msgErro.='Você tem que informar o Cargo do Servidor!\n';
                    $erro = 1;
                }
                
                # formata as datas quando vier de um controle html5 (vem yyyy/mm/dd)
                if(HTML5){
                    $dtAdmissao = date_to_php($dtAdmissao);
                    $dtNasc = date_to_php($dtNasc);
                }
                
                # verifica a validade da data de admissao
                if (!validaData($dtAdmissao)){
                    $msgErro.='A data de admissão não é válida!\n';
                    $erro = 1;
                }else{
                    $dtAdmissao = date_to_bd($dtAdmissao);
                }     
                
                # verifica a validade da data de Nascimento
                if (!validaData($dtNasc)){
                    $msgErro.='A data de Nascimento não é válida!\n';
                    $erro = 1;
                }else{
                    $dtNasc = date_to_bd($dtNasc);
                }     
                
                # Verifia se houve erro 
                if ($erro == 1){
                    alert($msgErro);
                    back(1);
                }else{
                    # Grava os dados

                    # Tabelas tbpessoa e tbdocumentacao
                    if(is_null($idPessoa)) // Verifica se a pessoa está/ou não cadastrada
                    {
                        # Gravar na tbpessoa
                        # dados
                        $campos = array('nome','dtNasc','sexo');
                        $valor = array($nome,$dtNasc,$sexo);
                        $idValor = NULL;
                        $tabela = 'tbpessoa';

                        # gravação
                        $pessoal->gravar($campos,$valor,$idValor,$tabela,NULL,FALSE);

                        # pega o id
                        $idPessoa = $pessoal->get_lastId();

                        ###################################

                        # Grava na tbdocumentacao
                        # dados
                        $campos = array('CPF','pisPasep','idPessoa');
                        $valor = array($cpf,$pisPasep,$idPessoa);
                        $idValor = NULL;
                        $tabela = 'tbdocumentacao';

                        # gravação
                        $pessoal->gravar($campos,$valor,$idValor,$tabela,NULL,FALSE);
                        
                         # pega o id
                         $idDocumentacao = $pessoal->get_lastId();
                    }

                    ###################################

                    # Grava na tbservidor
                    # dados
                    $campos = array('matricula','idPerfil','idPessoa','idCargo','dtAdmissao','situacao','idFuncional');
                    $valor = array($matricula,$perfil,$idPessoa,$cargo,$dtAdmissao,1,$idFuncional);
                    $idValor = NULL;
                    $tabela = 'tbservidor';

                    # gravação
                    $pessoal->gravar($campos,$valor,$idValor,$tabela,NULL,FALSE);
                    
                    # pega o id
                    $idServidor = $pessoal->get_lastId();
                    
                    # Grava no Log
                    $data = date("Y-m-d H:i:s");
                    
                    # tbservidor
                    $atividade = "Inclusão de servidor:[idPerfil]->".$perfil." [idPessoa]->".$idPessoa." [idCargo]->".$cargo." [dtAdmissao]->".$dtAdmissao;
                    $intra->registraLog($idUsuario,$data,$atividade,$tabela,$idServidor,1,$idServidor);
                    
                    # tbpessoa
                    $atividade = "Inclusão de servidor:[nome]->".$nome;
                    $intra->registraLog($idUsuario,$data,$atividade,'tbpessoa',$idPessoa,1,$idServidor);
                    
                    # tbdocumentacao 
                    $atividade = "Inclusão de servidor:[CPF]->".$cpf." [pisPasep]->".$pisPasep." [idPessoa]->".$cargo." [dtAdmissao]->".$dtAdmissao;
                    $intra->registraLog($idUsuario,$data,$atividade,'tbdocumentacao',$idDocumentacao,1,$idServidor);

                    ###################################

                    # Grava na tbhistlot
                    # dados
                    $campos = array('idServidor','lotacao','data','motivo');
                    $valor = array($idServidor,$lotacao,$dtAdmissao,'Lotação Inicial');
                    $idValor = NULL;
                    $tabela = 'tbhistlot';

                    # gravação
                    $pessoal->gravar($campos,$valor,$idValor,$tabela,NULL,FALSE);
                    
                    # Grava no Log
                    $atividade = "Lotação Inicial:[lotacao]->".$lotacao." [data]->".$dtAdmissao;
                    $intra->registraLog($idUsuario,$data,$atividade,$tabela,$idServidor,1,$idServidor);

                    ###################################

                    # Grava na tbprogressao
                    if($perfil == 1) // somente estatutários
                    {
                        # dados
                        $nivel = $pessoal->get_nivelCargoCargo($cargo); // nível do cargo
                        $plano = $pessoal->get_idPlanoAtual();      // plano de carcos atual
                        $classe = $pessoal->get_classeInicial($plano,$nivel);  // primeiro salário desse nível                

                        $campos = array('idServidor','idTpProgressao','dtInicial','idClasse');
                        $valor = array($idServidor,1,$dtAdmissao,$classe);
                        $idValor = NULL;
                        $tabela = 'tbprogressao';

                        # gravação
                        $pessoal->gravar($campos,$valor,$idValor,$tabela);
                        
                        # Grava no Log
                        $atividade = "Salário Inicial:[dtInicial]->".$dtAdmissao." [idClasse]->".$classe;
                        $intra->registraLog($idUsuario,$data,$atividade,$tabela,$idServidor,1,$idServidor);
                    }
                    
                    ###################################

                    # Grava na tbCedido
                    if($perfil == 2) // somente cedidos
                    {
                        $campos = array('idServidor');
                        $valor = array($idServidor);
                        $idValor = NULL;
                        $tabela = 'tbcedido';

                        # gravação
                        $pessoal->gravar($campos,$valor,$idValor,$tabela);
                    }
                    ###################################

                    # Carrega a página do servidor criado
                    set_session('idServidorPesquisado',$idServidor);
                    loadPage('servidorMenu.php');
                }
                   
                break;

    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}