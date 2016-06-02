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

if($acesso)
{    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');

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

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Servidores');

    # bot?o de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    #$objeto->set_parametroLabel('Pesquisar por nome, matrícula ou idFuncional:');
    #$objeto->set_parametroValue($parametro);

    # ordenação
    if(is_null($orderCampo))
            $orderCampo = "9,3";

    if(is_null($orderTipo))
            $orderTipo = 'asc';
    
    # sql
    $sql = 'SELECT tbfuncionario.matricula,
                   tbfuncionario.idFuncional,
                   tbpessoa.nome,
                   tbcargo.nome,
                   (SELECT tbtipocomissao.descricao FROM tbcomissao JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao) WHERE dtExo is NULL AND tbcomissao.matricula = tbfuncionario.matricula),
                   concat(tblotacao.UADM," - ",tblotacao.DIR," - ",tblotacao.GER) lotacao,
                   tbperfil.nome,
                   tbfuncionario.dtAdmissao,
                   tbsituacao.Sit
              FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                      JOIN tbhistlot ON (tbfuncionario.matricula = tbhistlot.matricula)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                 LEFT JOIN tbsituacao ON (tbfuncionario.sit = tbsituacao.idSit)
                                 LEFT JOIN tbperfil ON (tbfuncionario.idPerfil = tbperfil.idPerfil)
                                 LEFT JOIN tbcargo ON (tbfuncionario.idCargo = tbcargo.idCargo)
            WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.matricula = tbfuncionario.matricula)';
    
    if($parametroNomeMat <> '')
    {
        $sql .= ' AND ((tbpessoa.nome LIKE "%'.$parametroNomeMat.'%")
                   OR (tbfuncionario.matricula LIKE "%'.$parametroNomeMat.'%")
				   OR (tbfuncionario.idfuncional LIKE "%'.$parametroNomeMat.'%"))';
    }   
    
    if($parametroCargo <> "*")
        $sql .= ' AND (tbcargo.idcargo = "'.$parametroCargo.'")';
    
    if($parametroCargoComissao <> "*")
        $sql .= ' AND ((SELECT tbtipocomissao.descricao FROM tbcomissao JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao) WHERE dtExo is NULL AND tbcomissao.matricula = tbfuncionario.matricula) = "'.$parametroCargoComissao.'")';    
    
    if($parametroLotacao <> "*")
        $sql .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
    
     if($parametroPerfil <> "*")
        $sql .= ' AND (tbperfil.idperfil = "'.$parametroPerfil.'")';
     
     if($parametroSituacao <> "*")
        $sql .= ' AND (tbsituacao.idSit = "'.$parametroSituacao.'")';
    
     $sql .=' ORDER BY tbfuncionario.sit asc, '.$orderCampo.' '.$orderTipo;     
    
    # select da lista
    $objeto->set_selectLista ($sql);

    # ordem da lista
    $objeto->set_orderCampo($orderCampo);
    $objeto->set_orderTipo($orderTipo);
    $objeto->set_orderChamador('?fase=listar');

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    #$objeto->set_linkExcluir('?fase=excluir');
    #$objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label($label = array('Matricula','IdFuncional','Nome','Cargo','Cargo em Comissão','Lotação','Perfil','Admissão','Situação'));
    $objeto->set_width(array(5,5,20,15,10,15,10,10,5));
    $objeto->set_align(array("center","center","left","left","center","left"));
    $objeto->set_function(array ("dv",null,null,null,null,null,null,"date_to_php"));

    $objeto->set_formatacaoCondicional(array(array('coluna' => 8,
                                                   'valor' => 'Inativo',
                                                   'operador' => '=',
                                                   'id' => 'inativo')));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbfuncionario');

    # Nome do campo id
    $objeto->set_idCampo('matricula');

    ################################################################
    switch ($fase)
    {
        # Lista os Servidores
        case "" :
        case "listar" :
            botaoVoltar('grh.php');
            # Cadastro de Servidores 
            $grid = new Grid();
            $grid->abreColuna(12);
        
            # Parâmetros
            $form = new Form('?fase=listar');

                # Nome ou Matrícula
                $controle = new Input('parametroNomeMat','texto','Nome, matrícula ou IdFuncional:',1);
                $controle->set_size(55);
                $controle->set_title('Nome ou matrícula:');
                $controle->set_valor($parametroNomeMat);
                $controle->set_autofocus(true);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(4);
                $controle->set_fieldset('Filtro');
                $form->add_item($controle);
                
                # Situação
                $result = $pessoal->select('SELECT idsit, sit
                                              FROM tbsituacao                                
                                          ORDER BY 1');
                array_unshift($result,array('*','-- Todos --'));
                
                $controle = new Input('parametroSituacao','combo','Situação:',1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Situação');
                $controle->set_array($result);
                $controle->set_valor($parametroSituacao);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(2);
                $form->add_item($controle);
                
                # Cargos
                $result = $pessoal->select('SELECT tbcargo.idCargo, tbcargo.nome
                                              FROM tbcargo                                
                                     ORDER BY 1');
                array_unshift($result,array('*','-- Todos --'));
                
                $controle = new Input('parametroCargo','combo','Cargo:',1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Cargo');
                $controle->set_array($result);
                $controle->set_valor($parametroCargo);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(6);
                $form->add_item($controle);
                
                # Cargos em Comissão
                $result = $pessoal->select('SELECT tbtipocomissao.descricao,tbtipocomissao.descricao
                                              FROM tbtipocomissao                                
                                          ORDER BY 1');
                array_unshift($result,array('*','-- Todos --'));
                
                $controle = new Input('parametroCargoComissao','combo','Cargo em Comissão:',1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Cargo em Comissão');
                $controle->set_array($result);
                $controle->set_valor($parametroCargoComissao);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(2);
                $controle->set_col(3);
                $form->add_item($controle);
                
                # Lotação
                $result = $pessoal->select('SELECT idlotacao, concat(tblotacao.UADM," - ",tblotacao.DIR," - ",tblotacao.GER) lotacao
                                              FROM tblotacao                                
                                          ORDER BY ativo desc,lotacao');
                array_unshift($result,array('*','-- Todos --'));
                
                $controle = new Input('parametroLotacao','combo','Lotação:',1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Lotação');
                $controle->set_array($result);
                $controle->set_valor($parametroLotacao);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(2);
                $controle->set_col(7);
                $form->add_item($controle);
                
                # Perfil
                $result = $pessoal->select('SELECT idperfil, nome
                                              FROM tbperfil                                
                                          ORDER BY 1');
                array_unshift($result,array('*','-- Todos --'));
                
                $controle = new Input('parametroPerfil','combo','Perfil:',1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Perfil');
                $controle->set_array($result);
                $controle->set_valor($parametroPerfil);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(2);
                $controle->set_col(2);
                $form->add_item($controle);
                
            $form->show();
            $grid->fechaColuna();
            $grid->fechaGrid();
            
            $objeto->listar();
            break;

        ###############################

        # Chama o menu do Servidor que se quer editar
        case "editar" :
            if(is_null($id))
               loadPage('?fase=incluir');
            else
            {
                set_session('matriculaGrh',$id);
                loadPage('servidorMenu.php');
            }
            break; 

        ###############################

        # Inclusão de Novo Servidor    
        case "incluir" :
            # Cadastro de Servidores 
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Botão voltar
            botaoVoltar('servidor.php');

            # Fieldset
            $fieldset = new Fieldset('Incluir Novo Servidor - Página 1','inclusaoServidorPagina1');
            $fieldset->abre();

            $form = new Form('?fase=validaCPF','novoServidor');

            # CPF
            $controle = new Input('cpf','cpf','CPF:',1);
            $controle->set_size(20);            
            $controle->set_linha(1);
            $controle->set_col(6);
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
            
            # Mensagem
            $msg = new Alert('Para inclusão de um novo servidor é necessário que se informe o CPF para que o sistema verifique se o mesmo já está cadastrado em uma matrícula anterior.');
            $msg->set_tipo('primary');
            $msg->show();

            $fieldset->fecha();
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ###############################

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
            if ($valida->vazio($cpf)) 
            {
                $msgErro.='Você tem que digitar o CPF !!\n';
                $erro = 1;
            }

            # Verifica validade do CPF
            if (!$valida->cpf($cpf)) 
            {
                $msgErro.='CPF inválido !!\n';
                $erro = 1;
            }

            # Verifica se o CPF já está cadastrado
            $idPessoa = $pessoal->get_idpessoaCPF($cpf);

            if(!is_null($idPessoa))
            {
                # Servidor ativo
                if(!is_null($pessoal->get_idPessoaAtiva($idPessoa)))
                {
                    $msgErro.='Funcionário com matrícula ativa !! não pode ser incluído em outra matrícula !!\n';
                    $erro = 1;
                }
            }

            # Verifia se houve erro 
            if ($erro == 1)
            {
                $alert = new Alert($msgErro) ;
                $alert->show();

                back(1);
            }
            else
            {
                set_session('sessionCpf',$cpf);
                loadPage('?fase=incluir2');
            }

         break;

        ###############################

        # Continua o cadastro do servidor 
        case "incluir2" :

            # Pega o CPF da session
            $cpf = get_session('sessionCpf');

            # Variaveis de quando o servidor já for cadastrado
            $nome = null;
            $pis = null;

            # Verifica se o CPF já está cadastrado
            $idPessoa = $pessoal->get_idpessoaCPF($cpf);

            # pega o nome e o pis da pessoa (caso ja esteja cadastrado)
            if(!is_null($idPessoa))
            {
                $nome = $pessoal->get_nomeidPessoa($idPessoa);
                $pis = $pessoal->get_Pis($idPessoa);
            }

            # Botão voltar
            botaoVoltar('?fase=incluir');

            # Fieldset
            $fieldset = new Fieldset('Incluir Novo Servidor - Página 2','inclusaoServidorPagina2');
            $fieldset->abre();

            # Mensagem
            if(is_null($nome))
                $mensagem = 'O CPF '.$cpf.' não está cadastrado no sistema.';
            else
                $mensagem = 'O CPF '.$cpf.' já está cadastrado para o servidor INATIVO: '.$nome.'. Entre com os dados da sua nova matrícula.';
            
            # Mensagem
            p($mensagem,'inclusaoServidor');
            

            # Mensagem estatutários
            $matriculaPerfil = $pessoal->get_perfilMatricula(1);
            $mensagem = 'Estatutário - Faixa de matrícula permitida: de '.$matriculaPerfil[0].' até '.$matriculaPerfil[1];
            $box = new Div('divEstatutarios');
            $box->abre();
            p($mensagem,'inclusaoServidor');
            
            $box->fecha();

            # Mensagem cedidos
            $matriculaPerfil = $pessoal->get_perfilMatricula(2);
            $mensagem = 'Cedidos - Faixa de matrícula permitida: de '.$matriculaPerfil[0].' até '.$matriculaPerfil[1];
            $box = new Div('divCedidos');            
            $box->abre();
            p($mensagem,'inclusaoServidor');
            $box->fecha();
            
            # Mensagem convidados
            $matriculaPerfil = $pessoal->get_perfilMatricula(3);
            $mensagem = 'Convidados - Faixa de matrícula permitida: de '.$matriculaPerfil[0].' até '.$matriculaPerfil[1];
            $box = new Div('divConvidados');            
            $box->abre();
            p($mensagem,'inclusaoServidor');
            $box->fecha();
            
            # Mensagem estagiarios
            $matriculaPerfil = $pessoal->get_perfilMatricula(4);
            $mensagem = 'Estagiários - Faixa de matrícula permitida: de '.$matriculaPerfil[0].' até '.$matriculaPerfil[1];
            $box = new Div('divEstagiarios');            
            $box->abre();
            p($mensagem,'inclusaoServidor');
            $box->fecha(); 
            
            $form = new Form('?fase=validaDados','novoServidor');
            if(is_null($nome))
                $form->set_foco('nome');
            else
                $form->set_foco('perfil');

                # CPF
                $controle = new Input('cpf','cpf','CPF:',1);
                $controle->set_size(20);            
                $controle->set_linha(1);
                $controle->set_readonly(true);
                $controle->set_valor($cpf);
                $controle->set_title('O CPF do Novo Servidor');
                $form->add_item($controle);            

                # Nome
                $controle = new Input('nome','texto','Nome:',1);
                $controle->set_size(50);            
                $controle->set_linha(1);
                if(!is_null($nome))
                {
                    $controle->set_valor($nome);
                    $controle->set_readonly(true);                    
                }
                else
                    $controle->set_autofocus(true);
                
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
                $controle->set_linha(2);
                $controle->set_title('O perfil do Servidor.');
                $controle->set_array($perfil);
                $controle->set_onChange('exibeEscondeCampos();');
                $form->add_item($controle);

                # Mensagem sobre a matrícula
                $mensagem = 'Você tem a opção de digitar a matrícula ou deixar em branco para que o sistema gere automaticamente.
    Se uma matrícula for informada ela deve seguir os limítes definidos no cadastro de perfil.';
                $p = new P($mensagem,'inclusaoServidor2');
                #$p->show();
                $form->add_item($p);

                # Matrícula
                $controle = new Input('matricula','texto','Matrícula: (sem o dígito verificador)',1);
                $controle->set_size(20);            
                $controle->set_linha(3);                
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
                $controle->set_linha(4);                
                $controle->set_title('A Loteção do Servidor.');
                $controle->set_array($lotacao);
                $form->add_item($controle);

                # Data de Admissão
                $controle = new Input('dtAdmissao','date','Data de Admissão:',1);
                $controle->set_size(15);            
                $controle->set_linha(4);
                $controle->set_title('A data de admissão do servidor.');
                $form->add_item($controle);

                # PIS/Pasep
                if(is_null($pis))
                {                
                    $controle = new Input('pisPasep','texto','Pis/Pasep:',1);
                    $controle->set_size(12);            
                    $controle->set_linha(4);
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
                $controle->set_linha(5);                
                $controle->set_title('O Cargo do Servidor.');
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

            $fieldset->fecha(); 

            break;

            ###############################

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
                if ($valida->vazio($nome)) 
                {
                    $msgErro.='Você tem que informar o Nome do Servidor !!\n';
                    $erro = 1;
                }

                # Verifica se o Perfil foi digitado
                if ($valida->vazio($perfil)) 
                {
                    $msgErro.='Você tem que informar o Perfil do Servidor !!\n';
                    $erro = 1;
                }
                else
                {    # verificações da matrícula             
                    if((is_null($matricula)) OR ($matricula == ""))
                    {
                        # Gera uma nova matrícula
                        $matricula = $pessoal->get_novaMatricula($perfil);                
                    }

                    # Verifica se a matrícula já existe
                    if($pessoal->get_existeMatricula($matricula))
                    {
                        $msgErro.='Essa matrícula já está em uso!!\n';
                        $erro = 1;
                    }   

                    # verifica se a matricula está na faixa do perfil
                    $matriculaPerfil = $pessoal->get_perfilMatricula($perfil);

                    if(($matricula < $matriculaPerfil[0]) OR ($matricula > $matriculaPerfil[1]))
                    {                    
                        $msgErro.='Matrícula fora da faixa permitida por esse perfil!!\n';
                        $erro = 1;                  
                    }

                }

                # Verifica se a lotação foi digitada
                if ($valida->vazio($lotacao)) 
                {
                    $msgErro.='Você tem que informar a Lotação do Servidor !!\n';
                    $erro = 1;
                }

                # Verifica se a Admissão foi digitada
                if ($valida->vazio($dtAdmissao)) 
                {
                    $msgErro.='Você tem que informar a Data de Admissão do Servidor !!\n';
                    $erro = 1;
                }

                # Verifica o Pis              
                if(is_null($idPessoa)) // Verifica se a pessoa está cadastrada
                {        
                    # Verifica se o Pis foi digitado 
                    if ($valida->vazio($pisPasep)) 
                    {
                        $msgErro.='Você tem que informar o Pis/Pasep do Servidor !!\n';
                        $erro = 1;
                    }

                    # Verifica se o pis já existe
                    $idPessoaDuplicataPis = $pessoal->get_idpessoaPis($pisPasep);                
                    if(!is_null($idPessoaDuplicataPis))
                    {
                        $msgErro.='Esse Pis/Pasep já está cadastrado para o servidor: '.$pessoal->get_nomeidPessoa($idPessoaDuplicataPis).'!!\n';
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
                if (($perfil == 1) AND ($valida->vazio($cargo)))
                {
                    $msgErro.='Você tem que informar o Cargo do Servidor !!\n';
                    $erro = 1;
                }

                # formata data quando vier de um controle html5 (vem yyyy/mm/dd)
                if(HTML5)
                    $dtAdmissao = date_to_php($dtAdmissao);
                
                # verifica a validade da data de admissao
                if (!Data::validaData($dtAdmissao))
                {
                    $msgErro.='A data de admissão não é válida !!\n';
                    $erro = 1;
                }
                else
                    $dtAdmissao = date_to_bd($dtAdmissao);           

                # Verifia se houve erro 
                if ($erro == 1)
                {
                    $alert = new Alert($msgErro) ;
                    $alert->show();

                    back(1);
                }
                else
                {
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
                    loadPage('?fase=editar&id='.$matricula);
                }  
                break;

    }									 	 		

    $page->terminaPagina();
}
?>