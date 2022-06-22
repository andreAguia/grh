<?php

/**
 * Cadastro de MCF
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

    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $ano = arrayPreenche($anoInicial, $anoAtual, "d");

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de MCF";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', date('Y')));
    $parametroMes = post('parametroMes', get_session('parametroMes', date('m')));

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroMes', $parametroMes);

    # Começa uma nova página
    $page = new Page();
    if ($fase == "upload") {
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
    # Nome do Modelo
    $objeto->set_nome('Controle de MCF (Mapa de Controle de Frequência)');

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    $select = "SELECT idMcf,
                      ano,
                      mes,
                      idLotacao,
                      pagina,
                      idMcf,
                      idMcf
                 FROM tbmcf
                WHERE ano = '{$parametroAno}'";

    if ($parametroMes <> "*") {
        $select .= " AND mes = '{$parametroMes}'";
    }
    $select .= " ORDER BY ano desc,mes desc, pagina";

    # select da lista
    $objeto->set_selectLista($select);

    # select do edita
    $objeto->set_selectEdita('SELECT ano,
                                     mes,
                                     idLotacao,
                                     pagina,
                                     obs
                                FROM tbmcf
                               WHERE idMcf = ' . $id);

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Id", "Ano", "Mês", "Lotação", "Página", "Obs", " Ver"]);
    $objeto->set_width([8, 8, 10, 38, 8, 8, 8]);
    $objeto->set_align(["center", "center", "center", "left"]);
    $objeto->set_funcao([null, null, "get_nomeMes"]);

    $objeto->set_classe([null, null, null, "Pessoal", null, "Mcf", "Mcf"]);
    $objeto->set_metodo([null, null, null, "get_nomeLotacao2", null, "exibeObs", "exibeMcf"]);

    $objeto->set_rowspan([1, 2]);
    $objeto->set_grupoCorColuna(1);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbmcf');

    # Nome do campo id
    $objeto->set_idCampo('idMcf');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo lotacao
    $selectLotacao = 'SELECT idlotacao, 
                             concat(IFnull(UADM,"")," - ",IFnull(DIR,"")," - ",IFnull(GER,"")," - ",IFnull(nome,"")) as lotacao
                        FROM tblotacao 
                       WHERE ativo     
                     ORDER BY lotacao';

    $result = $pessoal->select($selectLotacao);
    array_unshift($result, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'combo',
            'array' => $ano,
            'required' => true,
            'autofocus' => true,
            'padrao' => $parametroAno,
            'col' => 3,
            'size' => 30),
        array('linha' => 1,
            'nome' => 'mes',
            'label' => 'Mes:',
            'tipo' => 'combo',
            'array' => $mes,
            'required' => true,
            'padrao' => $parametroMes == "*" ? date('m') : $parametroMes,
            'col' => 3,
            'size' => 30),
        array('linha' => 2,
            'nome' => 'idLotacao',
            'label' => 'Lotacão:',
            'tipo' => 'combo',
            'required' => true,
            'array' => $result,
            'size' => 20,
            'col' => 10,
            'title' => 'qual a lotação do MCF'),
        array('linha' => 2,
            'nome' => 'pagina',
            'label' => 'Página:',
            'tipo' => 'texto',
            'col' => 2,
            'size' => 25,
            'title' => 'Número da página.'),
        array('linha' => 2,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    # Botão de Upload 
    if (!empty($id)) {

        # Monta o arquivo
        $arquivo = PASTA_MCF . "{$id}.pdf";

        # Botão de Upload
        $botao = new Button("Arquivo PDF");
        $botao->set_url("?fase=upload&id={$id}");
        $botao->set_title("Faz o Upload, substitui ou exclui o arquivo PDF");
        $botao->set_target("_blank");

        $objeto->set_botaoEditarExtra([$botao]);
    }

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :

            # Retira so botões da classe modelo
            $objeto->set_botaoVoltarLista(false);
            $objeto->set_botaoIncluir(false);

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Incluir
            $botaoIncluir = new Link("Incluir", "?fase=editar");
            $botaoIncluir->set_class('button');
            $botaoIncluir->set_title('Inclui um novo resgistro');
            $menu1->add_link($botaoIncluir, "right");

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
            $controle->set_col(2);
            $form->add_item($controle);

            # Mês
            array_unshift($mes, array('*', '-- Todos --'));
            $controle = new Input('parametroMes', 'combo', 'Mês:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra pelo Mês');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->fechaGrid();

            ################################################################

            $objeto->listar();
            break;

        case "editar" :
        case "gravar" :
            $objeto->$fase($id);
            break;

        case "excluir" :
            # apaga o Bim relacionado
            if (file_exists(PASTA_MCF . "{$id}.pdf")) {
                rename(PASTA_MCF . "{$id}.pdf", PASTA_MCF . "apagado_{$id}_" . $intra->get_usuario($idUsuario) . "_" . date("Y.m.d_H:i") . ".pdf");
            }

            # Exclui o registro
            $objeto->excluir($id);
            break;

        ################################################################

        case "upload" :
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Dados a serem mudados
            $pasta = PASTA_MCF;
            $nome = "Mcf";
            $tabela = "tbmcf";

            # Extensões possíveis
            $extensoes = ["pdf"];

            # Exibe o Título
            if (!file_exists("{$pasta}{$id}.pdf")) {
                br();

                # Título
                tituloTable("Upload do Arquivo PDF ({$nome})");

                # do Log
                $atividade = "Fez o upload do {$nome}";
            } else {
                # Monta o Menu
                $menu = new MenuBar();

                $botaoApaga = new Button("Excluir o Arquivo PDF");
                $botaoApaga->set_url("?fase=apagaDocumento&id={$id}");
                $botaoApaga->set_title("Exclui o Arquivo PDF cadastrado");
                $botaoApaga->set_class("button alert");
                $botaoApaga->set_confirma('Tem certeza que você deseja excluir o arquivo PDF?');
                $menu->add_link($botaoApaga, "right");
                $menu->show();

                # Título
                tituloTable("Substituir o Arquivo PDF Cadastrado");

                # Define o link de voltar após o salvar
                $voltarsalvar = "?fase=uploadTerminado";

                # do Log
                $atividade = "Substituiu o arquivo PDF do {$nome}";
            }

            #####
            # Limita a tela
            $grid->fechaColuna();
            $grid->abreColuna(6);

            # Monta o formulário
            echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";

            # Se não existe o programa cria
            if (!file_exists($pasta) || !is_dir($pasta)) {
                mkdir($pasta, 0755);
            }

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
                    $Objetolog->registraLog($idUsuario, $data, $atividade, $tabela, $id, 8, $idServidorPesquisado);

                    # Fecha a janela aberta
                    loadPage("?fase=uploadTerminado");
                } else {
                    # volta a tela de upload
                    loadPage("?fase=upload&id=$id");
                }
            }

            # Informa caso exista um arquivo com o mesmo nome
            $arquivoDocumento = $pasta . $id . ".pdf";
            if (file_exists($arquivoDocumento)) {
                p("Já existe um documento para este registro!!<br/>"
                        . "O novo documento irá substituir o antigo !", "puploadMensagem");
                br();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "uploadTerminado" :
            # Informa que o bim foi substituído
            alert("PDF Cadastrado !!");

            # Fecha a janela
            echo '<script type="text/javascript" language="javascript">window.close();</script>';
            break;

        case "apagaDocumento" :

            # Dados a serem mudados
            $pasta = PASTA_MCF;
            $nome = "Mcf";
            $tabela = "tbmcf";

            # Apaga o arquivo (na verdade renomeia)
            if (rename("{$pasta}{$id}.pdf", "{$pasta}apagado_{$id}_" . $intra->get_usuario($idUsuario) . "_" . date("Y.m.d_H:i") . ".pdf")) {
                alert("Arquivo Excluído !!");

                # Registra log
                $atividade = "Excluiu o arquivo PDF do {$nome}";
                $Objetolog = new Intra();
                $data = date("Y-m-d H:i:s");
                $Objetolog->registraLog($idUsuario, $data, $atividade, $tabela, $id, 3, $idServidorPesquisado);

                # Fecha a janela
                echo '<script type="text/javascript" language="javascript">window.close();</script>';
            } else {
                alert("Houve algum problema, O arquivo não pode ser excluído !!");

                # Fecha a janela
                echo '<script type="text/javascript" language="javascript">window.close();</script>';
            }

            break;

        ##################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}