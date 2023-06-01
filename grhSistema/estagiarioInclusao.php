<?php

/**
 * Cadastro de Servidores
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase', 'incluir');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

####################################################################################
    switch ($fase) {
        # Inclusão de Novo Servidor    
        case "incluir" :
            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Botão voltar
            botaoVoltar('servidor.php');

            # Título
            titulo('Incluir Novo Estagiário / Bolsista');
            $callout = new Callout();
            $callout->abre();

            # Inicia o formulário
            $form = new Form('?fase=validaCPF', 'novoServidor');

            # CPF
            $controle = new Input('cpf', 'cpf', 'CPF:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_required(true);
            $controle->set_autofocus(true);
            $controle->set_title('O CPF do Novo Servidor');
            $form->add_item($controle);

            # Perfil                
            $perfil = $pessoal->select('SELECT idperfil,
                                               nome
                                          FROM tbperfil
                                         WHERE tipo = "Outros"   
                                      ORDER BY nome');

            array_unshift($perfil, array(null, null));

            $controle = new Input('perfil', 'combo', 'Perfil:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_required(true);
            $controle->set_col(3);
            $controle->set_title('O perfil do Servidor.');
            $controle->set_array($perfil);
            $controle->set_onChange('exibeEscondeCampos();');
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_valor(' Incluir ');
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
        # Valida o CPF e verifica se tem servidor cadastrado com esse CPF    
        case "validaCPF" :
            # Limita a Tela 
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Flag de erro: 1 - tem erro; 0 - não tem	
            $erro = 0;

            # Repositório de mensagens de erro        
            $msgErro = null;

            # Pega os valores digitados
            $cpf = post('cpf');
            $perfil = post('perfil');

            # Verifica se o CPF foi digitado
            if (empty($cpf)) {
                $msgErro .= 'Você tem que digitar o CPF!\n';
                $erro = 1;
            }

            # Verifica validade do CPF
            if (!validaCpf($cpf)) {
                $msgErro .= 'CPF inválido!\n';
                $erro = 1;
            }

            # Verifica se o CPF já está cadastrado
            if ($erro <> 1) {
                $idPessoa = $pessoal->get_idPessoaCPF($cpf);
            }

            # Verifia se houve erro 
            if ($erro == 1) {
                alert($msgErro);
                back(1);
            } else {
                set_session('sessionCpf', $cpf);
                set_session('sessionPerfil', $perfil);

                # Verifica se já existe servidor
                if (!is_null($idPessoa)) {

                    # Servidor ativo
                    if (!is_null($pessoal->get_idPessoaAtiva($idPessoa))) {

                        $grid->fechaColuna();
                        $grid->abreColuna(8);
                        br(3);

                        titulotable('Atenção!, Já existe um servidor ativo com este CPF!');
                        $callout = new Callout("warning");
                        $callout->abre();

                        p("Caso você esteja querendo incluir um vínculo antigo"
                                . " já inativo não tem problema, "
                                . "clique em Incluir mesmo assim.<br/><br/>"
                                . "Agora caso você esteja querendo incluir um "
                                . "novo vínculo, é necessário terminar o vínculo "
                                . "atualmente ativo. Desta forma clique em Voltar.");

                        # Cria um menu
                        $menu1 = new MenuBar();
                        br();

                        # Voltar
                        $botaoVoltar = new Link("Voltar", "cadastroEstagiario.php");
                        $botaoVoltar->set_class('button');
                        $botaoVoltar->set_title('Voltar a página anterior');
                        $botaoVoltar->set_accessKey('V');
                        $menu1->add_link($botaoVoltar, "left");

                        # Incluir
                        $botaoInserir = new Button("Incluir mesmo assim", "?fase=incluir2");
                        $botaoInserir->set_title("Incluir um Estagiário / Bolsista");
                        $menu1->add_link($botaoInserir, "right");
                        $menu1->show();

                        $callout->fecha();
                    } else {
                        loadPage('?fase=incluir2');
                    }
                } else {
                    set_session('sessionCpf', $cpf);
                    loadPage('?fase=incluir2');
                }
            }
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

####################################################################################
        # Continua o cadastro do servidor 
        case "incluir2" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Pega os valores já digitados
            $cpf = get_session('sessionCpf');
            $perfil = get_session('sessionPerfil');

            # Verifica se o CPF já está cadastrado
            $idPessoa = $pessoal->get_idPessoaCPF($cpf);

            # Inicia flag de perfil ativo
            $temAtivo = false;

            # Verifica se já tem alguém com esse cpf
            if (!empty($idPessoa)) {
                # Pega os dados da pessoa (caso ja esteja cadastrado)
                $nome = $pessoal->get_nomeidPessoa($idPessoa);
                $dtNasc = date_to_bd($pessoal->get_dataNascimentoIdPessoa($idPessoa));
                $sexo = $pessoal->get_sexoidPessoa($idPessoa);

                # Verifica se tem vínculo ativo
                if (!is_null($pessoal->get_idPessoaAtiva($idPessoa))) {
                    $temAtivo = true;
                }
            } else {
                $nome = null;
                $dtNasc = null;
                $sexo = null;
            }

            # Botão voltar
            botaoVoltar('?fase=incluir');

            # Título
            titulo('Incluir Novo Servidor');
            $callout = new Callout();
            $callout->abre();

            # Mensagem
            if (is_null($nome)) {
                $mensagem = 'O CPF ' . $cpf . ' não está cadastrado no sistema, dessa forma um novo servidor será incluído.';
            } else {
                if ($temAtivo) {
                    $mensagem = 'O CPF ' . $cpf . ' já está cadastrado para o servidor ATIVO: ' . $nome . '.<br/>Somente será permitido a inclusão de um vínculo INATIVO.<br/>Entre com os outros dados.';
                } else {
                    $mensagem = 'O CPF ' . $cpf . ' já está cadastrado para o servidor: ' . $nome . '.<br/>Entre com os outros dados.';
                }
            }

            # Mensagem do cpf
            br();
            callout($mensagem);

            $form = new Form('?fase=validaDados', 'novoServidor');

            # CPF
            $controle = new Input('cpf', 'cpf', 'CPF:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_readonly(true);
            $controle->set_valor($cpf);
            $controle->set_required(true);
            $controle->set_col(2);
            $controle->set_title('O CPF do Novo Servidor');
            $form->add_item($controle);

            # Perfil                
            $comboPerfil = $pessoal->select('SELECT idPerfil,
                                                    nome
                                               FROM tbperfil
                                           ORDER BY idPerfil');

            $controle = new Input('perfil', 'combo', 'Perfil:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_readonly(true);
            $controle->set_disabled(true);
            $controle->set_required(true);
            $controle->set_valor($perfil);
            $controle->set_col(3);
            $controle->set_title('O perfil do Servidor.');
            $controle->set_array($comboPerfil);
            $form->add_item($controle);

            # Nome
            $controle = new Input('nome', 'texto', 'Nome:', 1);
            $controle->set_size(50);
            $controle->set_col(6);
            $controle->set_linha(2);
            $controle->set_required(true);
            if (!is_null($nome)) {
                $controle->set_valor($nome);
                $controle->set_readonly(true);
            } else {
                $controle->set_autofocus(true);
            }
            $controle->set_title('O nome do servidor.');
            $form->add_item($controle);

            # Data de Nascimento
            $controle = new Input('dtNasc', 'date', 'Data de Nascimento:', 1);
            $controle->set_size(15);
            $controle->set_col(3);
            $controle->set_required(true);
            if (!is_null($dtNasc)) {
                $controle->set_valor($dtNasc);
                $controle->set_readonly(true);
            }
            $controle->set_linha(2);
            $controle->set_title('A data de nascimento do servidor.');
            $form->add_item($controle);

            # Sexo
            $controle = new Input('sexo', 'combo', 'Sexo:', 1);
            $controle->set_size(15);
            $controle->set_col(2);
            $controle->set_array(array(null, "Masculino", "Feminino"));
            $controle->set_required(true);
            if (!is_null($sexo)) {
                $controle->set_valor($sexo);
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $controle->set_linha(2);
            $controle->set_title('Sexo do Servidor.');
            $form->add_item($controle);

            # Lotação               
            $lotacao = $pessoal->select('SELECT idLotacao, 
                                                 concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) as lotacao,
                                                 if(ativo = 1,"Lotações Ativas","Lotações Inativas")
                                            FROM tblotacao 
                                        ORDER BY ativo DESC, lotacao');

            array_unshift($lotacao, array(null, null)); # Adiciona o valor de nulo

            $controle = new Input('lotacao', 'combo', 'Lotação Inicial:', 1);
            $controle->set_size(20);
            $controle->set_linha(3);
            $controle->set_col(6);
            $controle->set_optgroup(true);
            $controle->set_required(true);
            $controle->set_title('A Lotação do Servidor.');
            $controle->set_array($lotacao);
            $form->add_item($controle);

            # Data de Admissão
            $controle = new Input('dtAdmissao', 'date', 'Data de Admissão:', 1);
            $controle->set_size(15);
            $controle->set_col(3);
            $controle->set_required(true);
            $controle->set_linha(3);
            $controle->set_title('A data de admissão do servidor.');
            $form->add_item($controle);

            # Se já existir um servidor ativo
            if ($temAtivo) {

                # Pega os dados da combo situação
                $situacao = $pessoal->select('SELECT idSituacao,
                                                     situacao
                                                FROM tbsituacao
                                               WHERE idSituacao <> 1 
                                            ORDER BY situacao');

                array_unshift($situacao, array(null, null));

                $controle = new Input('situacao', 'combo', 'Situação:', 1);
                $controle->set_size(15);
                $controle->set_linha(4);
                $controle->set_required(true);
                $controle->set_title('A situação do servidor.');
                $controle->set_col(2);
                $controle->set_array($situacao);
                $form->add_item($controle);

                # Pega os dados da combo motivo de Saída do servidor
                $motivo = $pessoal->select('SELECT idmotivo,
                                       motivo
                                  FROM tbmotivo
                              ORDER BY motivo');

                array_unshift($motivo, array(null, null));

                $controle = new Input('motivo', 'combo', 'Motivo:', 1);
                $controle->set_size(15);
                $controle->set_linha(4);
                $controle->set_title('Motivo da Saida do Servidor.');
                $controle->set_col(4);
                $controle->set_array($motivo);
                $form->add_item($controle);

                # Data de Saída
                $controle = new Input('dtDemissao', 'date', 'Data de Saída:', 1);
                $controle->set_size(15);
                $controle->set_col(3);
                $controle->set_required(true);
                $controle->set_linha(4);
                $controle->set_title('A data de saída do servidor da UENF.');
                $form->add_item($controle);
            }

            # Matrícula
            $controle = new Input('matricula', 'texto', 'Matrícula Uenf: (sem o dígito verificador)', 1);
            $controle->set_size(20);
            $controle->set_linha(5);
            $controle->set_col(3);
            $controle->set_title('A matrícula do servidor.');
            $controle->set_helptext("Se não souber deixa em branco.");
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
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

            # Flag de erro: 1 - tem erro; 0 - não tem
            $erro = 0;

            # Repositório de mensagens de erro
            $msgErro = null;

            # Pega os valores da session
            $cpf = get_session('sessionCpf');
            $perfil = get_session('sessionPerfil');

            # Pega os valores digitados
            $matricula = post('matricula');
            $idFuncional = post('idFuncional');
            $lotacao = post('lotacao');
            $dtAdmissao = post('dtAdmissao');
            $situacao = post('situacao');

            $classe = null;
            $idPessoa = $pessoal->get_idPessoaCPF($cpf);

            # Inicia flag de perfil ativo
            $temAtivo = false;

            # Verifica se já tem alguém com esse cpf
            if (!empty($idPessoa)) {
                # Pega os dados da pessoa (caso ja esteja cadastrado)
                $nome = $pessoal->get_nomeidPessoa($idPessoa);
                $dtNasc = date_to_bd($pessoal->get_dataNascimentoIdPessoa($idPessoa));
                $sexo = $pessoal->get_sexoidPessoa($idPessoa);

                # Verifica se tem vínculo ativo
                if (!is_null($pessoal->get_idPessoaAtiva($idPessoa))) {
                    $temAtivo = true;
                    $dtDemissao = post('dtDemissao');
                    $motivo = post('motivo');
                }
            } else {
                # Senão pega os dados do post
                $sexo = post('sexo');
                $nome = post('nome');
                $dtNasc = post('dtNasc');
            }

            # Verifica se o Nome foi digitado
            if (empty($nome)) {
                $msgErro .= 'Você tem que informar o Nome do Servidor!\n';
                $erro = 1;
            }

            # Verifica se o Perfil foi digitado
            if (empty($perfil)) {
                $msgErro .= 'Você tem que informar o Perfil do Servidor!\n';
                $erro = 1;
            } else {
                # Verifica se o Perfil permite novo servidor
                if (!$pessoal->podeNovoServidor($perfil)) {
                    $msgErro .= 'Esse perfil não permite novo servidor!\n';
                    $erro = 1;
                }
            }

            # Verifica se a matrícula já existe
            if (!empty($matricula)) {
                if ($pessoal->get_existeMatricula($matricula)) {
                    $msgErro .= 'Essa matrícula já está em uso!\n';
                    $erro = 1;
                }
            } else {
                if (!empty($perfil)) {
                    $matricula = $pessoal->get_novaMatricula($perfil);
                }
            }

            # Verifica se a lotação foi digitada
            if (empty($lotacao)) {
                $msgErro .= 'Você tem que informar a Lotação do Servidor!\n';
                $erro = 1;
            }

            # Verifica se a Admissão foi digitada
            if (empty($dtAdmissao)) {
                $msgErro .= 'Você tem que informar a Data de Admissão do Servidor!\n';
                $erro = 1;
            }

            # Verifica se a data de nascimento foi digitada
            if (empty($dtNasc)) {
                $msgErro .= 'Você tem que informar a Data de Nascimento do Servidor!\n';
                $erro = 1;
            }

            # formata as datas quando vier de um controle data (vem yyyy/mm/dd)
            $dtAdmissao = date_to_php($dtAdmissao);
            $dtNasc = date_to_php($dtNasc);

            # verifica a validade da data de admissao
            if (!validaData($dtAdmissao)) {
                $msgErro .= 'A data de admissão não é válida!\n';
                $erro = 1;
            } else {
                $dtAdmissao = date_to_bd($dtAdmissao);
            }

            # verifica a validade da data de Nascimento
            if (!validaData($dtNasc)) {
                $msgErro .= 'A data de Nascimento não é válida!\n';
                $erro = 1;
            } else {
                $dtNasc = date_to_bd($dtNasc);
            }

            # Verifia se houve erro 
            if ($erro == 1) {
                alert($msgErro);
                back(1);
            } else {
                # Grava os dados
                # Tabelas tbpessoa e tbdocumentacao
                if (empty($idPessoa)) { // Verifica se a pessoa está/ou não cadastrada
                    # Gravar na tbpessoa
                    # dados
                    $campos = array('nome', 'dtNasc', 'sexo');
                    $valor = array($nome, $dtNasc, $sexo);
                    $idValor = null;
                    $tabela = 'tbpessoa';

                    # gravação
                    $pessoal->gravar($campos, $valor, $idValor, $tabela, null, false);

                    # pega o id
                    $idPessoa = $pessoal->get_lastId();

                    ###################################
                    # Grava na tbdocumentacao
                    # dados
                    $campos = array('CPF', 'idPessoa');
                    $valor = array($cpf, $idPessoa);
                    $idValor = null;
                    $tabela = 'tbdocumentacao';

                    # gravação
                    $pessoal->gravar($campos, $valor, $idValor, $tabela, null, false);

                    # pega o id
                    $idDocumentacao = $pessoal->get_lastId();
                }

                ###################################
                # Grava na tbservidor
                # dados
                if ($temAtivo) {
                    $campos = array('matricula', 'idPerfil', 'idPessoa', 'dtAdmissao', 'situacao', 'dtDemissao', 'motivo');
                    $valor = array($matricula, $perfil, $idPessoa, $dtAdmissao, $situacao, $dtDemissao, $motivo);
                } else {
                    $campos = array('matricula', 'idPerfil', 'idPessoa', 'dtAdmissao', 'situacao');
                    $valor = array($matricula, $perfil, $idPessoa, $dtAdmissao, 1);
                }
                $idValor = null;
                $tabela = 'tbservidor';

                # gravação
                $pessoal->gravar($campos, $valor, $idValor, $tabela, null, false);

                # pega o id
                $idServidor = $pessoal->get_lastId();

                # Grava no Log
                $data = date("Y-m-d H:i:s");

                # tbservidor
                $atividade = "Inclusão de {$pessoal->get_perfilNome($perfil)}<br/>"
                        . "[idPessoa]->{$idPessoa}<br/>"
                        . "[dtAdmissao]->{$dtAdmissao}<br/>";
                
                $intra->registraLog($idUsuario, $data, $atividade, $tabela, $idServidor, 1, $idServidor);

                # tbpessoa
                $atividade = "Inclusão de {$pessoal->get_perfilNome($perfil)}<br/>[nome]->{$nome}";
                $intra->registraLog($idUsuario, $data, $atividade, 'tbpessoa', $idPessoa, 1, $idServidor);

                # tbdocumentacao 
                if (empty($idPessoa)) {
                    $atividade = "Inclusão de {$pessoal->get_perfilNome($perfil)}<br/>[CPF]->{$cpf}<br/>[dtAdmissao]->{$dtAdmissao}";
                    $intra->registraLog($idUsuario, $data, $atividade, 'tbdocumentacao', $idDocumentacao, 1, $idServidor);
                }

                ###################################
                # Grava na tbhistlot
                # dados
                $campos = array('idServidor', 'lotacao', 'data', 'motivo');
                $valor = array($idServidor, $lotacao, $dtAdmissao, 'Lotação Inicial');
                $idValor = null;
                $tabela = 'tbhistlot';

                # gravação
                $pessoal->gravar($campos, $valor, $idValor, $tabela, null, false);

                # Grava no Log
                $atividade = "Inclusão de {$pessoal->get_perfilNome($perfil)}<br/>[lotacao]->{$pessoal->get_nomeLotacao($lotacao)}<br/>[data]->" . date_to_php($dtAdmissao);
                $intra->registraLog($idUsuario, $data, $atividade, $tabela, $idServidor, 1, $idServidor);

                ###################################
                # Carrega a página do servidor criado
                set_session('idServidorPesquisado', $idServidor);
                set_session('origem', 'cadastroEstagiario.php');
                loadPage('servidorMenu.php');
            }

            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}