<?php

/**
 * Cadastro de Perfil
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
    $fase = get('fase', 'listar');
    $subFase = get('subFase', 1);

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de publicação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametro = post('parametro', get_session('parametro'));
    $parametroTipo = post('parametroTipo', get_session('parametroTipo'));
    $parametroAno = post('parametroAno', get_session('parametroAno', date('Y')));
    $parametroMes = post('parametroMes', get_session('parametroMes', date('m')));
    $parametroLotacao = 66;

    $impactado = post('impactado');

    # Joga os parâmetros par as sessions
    set_session('parametroTipo', $parametroTipo);
    set_session('parametroAno', $parametroAno);
    set_session('parametroMes', $parametroMes);

    $page = new Page();
    if ($fase == "uploadPublicacao") {
        $page->set_ready('$(document).ready(function(){
                                $("form input").change(function(){
                                    $("form p").text(this.files.length + " arquivo(s) selecionado");
                                });
                            });');
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nosfildset e no caption da tabela)
    if ($parametroMes <> 0) {
        $objeto->set_nome("Publicações de " . get_nomeMes($parametroMes) . " de {$parametroAno}");
    } else {
        $objeto->set_nome("Publicações de {$parametroAno}");
    }

    # bot?o de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # select da lista
    $select = 'SELECT idPublicacao,
                      data,
                      pag,
                      tbtipopublicacao.nome,
                      descricao,
                      idPublicacao,
                      idPublicacao
                 FROM tbpublicacao JOIN tbtipopublicacao USING (idTipoPublicacao)
                WHERE descricao LIKE "%' . $parametro . '%"';

    if ($parametroMes <> 0) {
        $select .= ' AND MONTH(data) =   "' . $parametroMes . '"';
    }

    if ($parametroTipo <> 0) {
        $select .= ' AND idTipoPublicacao =   ' . $parametroTipo;
    }

    $select .= ' AND YEAR(data) = "' . $parametroAno . '" ORDER BY data';

    $objeto->set_selectLista($select);

    # select do edita
    $objeto->set_selectEdita("SELECT descricao,
                                     idTipoPublicacao,
                                     data,
                                     pag,
                                     idConcurso
                               FROM tbpublicacao
                              WHERE idPublicacao = {$id}");

    # Caminhos
    $objeto->set_linkEditar('?fase=editarPublicacao');
    #$objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["id", "Data", "Página", "Tipo", "Descrição", "Servidores Impactados", "Ver"]);
    $objeto->set_align(["center", "center", "center", "center", "left", "left"]);
    $objeto->set_width([5, 10, 5, 10, 35, 30, 5]);
    $objeto->set_funcao([null, "date_to_php"]);
    $objeto->set_classe([null, null, null, null, null, "Publicacao", "Publicacao"]);
    $objeto->set_metodo([null, null, null, null, null, "exibeServidoresImpactadosTabela", "exibePdf"]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbpublicacao');

    # Nome do campo id
    $objeto->set_idCampo('idPublicacao');

    # Combo concurso
    $select = "SELECT idConcurso,
                      concat(anoBase,' - Edital: ',DATE_FORMAT(dtPublicacaoEdital,'%d/%m/%Y')) as concurso
                 FROM tbconcurso
             ORDER BY dtPublicacaoEdital desc";

    # Pega os dados da combo concurso
    $concurso = $pessoal->select($select);
    array_unshift($concurso, array(null, null));

    # Combo tipo
    $select = "SELECT idTipoPublicacao,
                      nome
                 FROM tbtipopublicacao
             ORDER BY nome";

    # Pega os dados da combo concurso
    $tipo = $pessoal->select($select);
    array_unshift($tipo, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'descricao',
            'title' => 'Descrição da publicação',
            'label' => 'Descrição:',
            'tipo' => 'texto',
            'required' => true,
            'autofocus' => true,
            'col' => 12,
            'size' => 250),
        array('linha' => 2,
            'nome' => 'idTipoPublicacao',
            'title' => 'Tipo da Publicação',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'required' => true,
            'array' => $tipo,
            'col' => 3,
            'size' => 20),
        array('linha' => 2,
            'nome' => 'idConcurso',
            'label' => 'Concurso:',
            'tipo' => 'combo',
            'array' => $concurso,
            'col' => 4,
            'size' => 20),
        array('linha' => 2,
            'nome' => 'data',
            'label' => 'Data:',
            'tipo' => 'data',
            'title' => 'Data da publicação',
            'col' => 3,
            'size' => 15),
        array('linha' => 2,
            'nome' => 'pag',
            'label' => 'Página:',
            'tipo' => 'texto',
            'size' => 6,
            'title' => 'Página da publicação',
            'col' => 2)));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################
    switch ($fase) {
        case "" :
        case "listar" :

            $grid = new Grid();
            $grid->abreColuna(12);

            # Retira os botões padrão da classe
            $objeto->set_botaoVoltarLista(false);
            $objeto->set_botaoIncluir(false);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Cadastro de Tipos   
            $botaoTipo = new Button("Tipos", "cadastroTipoPublicacao.php");
            $botaoTipo->set_title("Cadastro de Tipos de Publicação");
            $menu1->add_link($botaoTipo, "right");

            # Incluir 
            $botaoTipo = new Button("Incluir", "?fase=editar");
            $botaoTipo->set_title("Cadastro de Tipos de Publicação");
            $menu1->add_link($botaoTipo, "right");

            $menu1->show();

            ################################################################
            # Formulário de Pesquisa
            $form = new Form('?');

            # Cria um array com os anos possíveis
            $anoInicial = 1999;
            $anoAtual = date('Y');
            $anoExercicio = arrayPreenche($anoInicial, $anoAtual, "d");

            $controle = new Input('parametroAno', 'combo', 'Ano:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anoExercicio);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_autofocus(true);
            $controle->set_col(2);
            $form->add_item($controle);

            # Mês
            array_unshift($mes, array(0, "Todos"));
            $controle = new Input('parametroMes', 'combo', 'Mês:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra pelo Mês');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            # Texto
            $controle = new Input('parametro', 'texto', 'Descrição ou servidores:', 1);
            $controle->set_size(100);
            $controle->set_title('Pesquisa pelo texto da descrição ou nome do servidor');
            $controle->set_valor($parametro);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $form->show();

            ################################################################
            $objeto->listar();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "editar" :
        case "excluir" :
        case "gravar" :
            $objeto->$fase($id);
            break;

        ################################################################

        case "editarPublicacao" :

            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title("Voltar");
            $menu->add_link($linkVoltar, "left");

            # Incluir Servidor
            $linkVoltar = new Link("Incluir Servidor", "?fase=incluirServidor&id={$id}");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title("Inclui servidor impactado");
            $menu->add_link($linkVoltar, "right");
            $menu->show();

            # Exibe a publicação
            $publicacao = new Publicacao();
            $publicacao->exibePublicacao($id);

            # Exibe os servidores impactados
            $publicacao->exibeServidoresImpactados($id);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "incluirServidor" :

            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Cancelar", "?fase=editarPublicacao&id={$id}");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title("Voltar");
            $menu->add_link($linkVoltar, "left");
            $menu->show();

            # Exibe a publicação
            $publicacao = new Publicacao();
            $publicacao->exibePublicacao($id);

            # Pega os dados da combo de servidor
            $membro = $pessoal->select('SELECT idServidor,
                                               CONCAT(tbpessoa.nome," - ",IFNULL(tbtipocargo.sigla,"")," - ",IFNULL(tbcargo.nome,"")),
                                               CONCAT(tbsituacao.situacao,"s")
                                  FROM uenf_grh.tbservidor JOIN uenf_grh.tbpessoa USING (idPessoa)
                                                      LEFT JOIN uenf_grh.tbsituacao ON (uenf_grh.tbservidor.situacao = uenf_grh.tbsituacao.idsituacao)
                                                      LEFT JOIN uenf_grh.tbcargo USING (idCargo)
                                                      LEFT JOIN uenf_grh.tbtipocargo USING (idTipoCargo)
                                 WHERE (idPerfil = 1 OR idPerfil = 2 OR idPerfil = 3 OR idPerfil = 4)
                              ORDER BY tbsituacao.idSituacao,
                                       tbpessoa.nome');
            array_unshift($membro, array(null, null));

            # Formulário de Pesquisa
            $form = new Form("?fase=validaServidor&id={$id}");

            $controle = new Input('impactado', 'combo', 'Servidor:', 1);
            $controle->set_size(30);
            $controle->set_title('Inclui servidor impactado');
            $controle->set_array($membro);
            $controle->set_valor($impactado);
            $controle->set_linha(1);
            $controle->set_optgroup(true);
            $controle->set_col(12);
            $form->add_item($controle);

            $controle = new Input('submit', 'submit');
            $controle->set_valor('Incluir');
            $controle->set_size(20);
            $controle->set_linha(2);
            $controle->set_col(3);

            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "validaServidor" :

            # Pega os dados
            $impactado = post("impactado");

            # Grava
            $pessoal->set_tabela("tbpublicacaoservidor");
            $pessoal->set_idCampo("idPublicacaoServidor");
            $pessoal->gravar(["idPublicacao", "idServidor"], [$id, $impactado]);

            # Log
            $atividade = 'Incluiu o servidor: ' . $pessoal->get_nome($impactado) . ' na publicação ' . $id;
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, "tbpublicacaoservidor", $pessoal->get_lastId(), 1, $impactado);

            aguarde();
            loadPage("?fase=editarPublicacao&id={$id}");
            break;

        ################################################################

        case "uploadPublicacao" :
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Botão voltar
            botaoVoltar('?');

            # Título
            tituloTable("Upload de Publicações");

            # Limita a tela
            $grid->fechaColuna();
            $grid->abreColuna(6);

            # Monta o formulário
            echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";

            # Pasta onde será guardado o arquivo
            $pasta = PASTA_PUBLICACAO;

            # Se não existe o programa cria
            if (!file_exists($pasta) || !is_dir($pasta)) {
                mkdir($pasta, 0755);
            }

            # Extensões possíveis
            $extensoes = array("pdf");

            # Pega os valores do php.ini
            $postMax = limpa_numero(ini_get('post_max_size'));
            $uploadMax = limpa_numero(ini_get('upload_max_filesize'));
            $limite = menorValor(array($postMax, $uploadMax));

            $texto = "Extensões Permitidas:";
            foreach ($extensoes as $pp) {
                $texto .= " $pp";
            }
            $texto .= "<br/>Tamanho Máximo do Arquivo: $limite M";

            br();
            p($texto, "f14", "center");

            if ((isset($_POST["submit"])) && (!empty($_FILES['doc']))) {
                $upload = new UploadDoc($_FILES['doc'], $pasta, $id, $extensoes);

                # Salva e verifica se houve erro
                if ($upload->salvar()) {

                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H:i:s");
                    $atividade = "Fez o upload da publicação no DOERJ";
                    $Objetolog->registraLog($idUsuario, $data, $atividade, null, $id, 8);

                    # Volta para o menu
                    loadPage("?fase=listar");
                } else {
                    loadPage("?fase=uploadPublicacao&id=$id");
                }
            }

            # Informa caso exista um arquivo com o mesmo nome
            $arquivoDocumento = $pasta . $id . ".pdf";
            if (file_exists($arquivoDocumento)) {
                p("Já existe um documento para este registro no servidor!!<br/>O novo documento irá sobrescrevê-lo e o antigo será apagado !!", "puploadMensagem");
                br();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ##########################################################################################
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}