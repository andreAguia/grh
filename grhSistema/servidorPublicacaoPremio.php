<?php

/**
 * Cadastro de Publicação de Licenças Prêmios
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $licenca = new LicencaPremio();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

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
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Publicações de Licança Prêmio');

    # bot?o de voltar da lista
    $objeto->set_voltarLista('servidorLicencaPremio.php');

    # controle de pesquisa
    #$objeto->set_parametroLabel('Pesquisar');
    #$objeto->set_parametroValue($parametro);
    # select da lista
    $objeto->set_selectLista('SELECT dtPublicacao,
                                     idPublicacaoPremio,
                                     numDias,
                                     idPublicacaoPremio,
                                     idPublicacaoPremio,
                                     idPublicacaoPremio,
                                     obs,
                                     idPublicacaoPremio                                     
                                FROM tbpublicacaopremio
                                WHERE idServidor = ' . $idServidorPesquisado . '
                             ORDER BY dtInicioPeriodo desc');

    # select do edita
    $objeto->set_selectEdita('SELECT dtPublicacao,
                                     dtInicioPeriodo,
                                     dtFimPeriodo,
                                     numDias,
                                     obs,
                                     idServidor
                                FROM tbpublicacaopremio
                               WHERE idPublicacaoPremio = ' . $id);

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
    $objeto->set_label(["Data da Publicação", "Período Aquisitivo", "Dias <br/> Publicados", "Dias <br/> Fruídos", "Dias <br/> Disponíveis", "DO", "Obs"]);
    $objeto->set_width([10, 15, 10, 10, 10, 10, 20]);
    $objeto->set_align(["center", "center", "center", "center", "center", "center", "left"]);
    $objeto->set_funcao(['date_to_php']);
    $objeto->set_classe([null, 'LicencaPremio', null, 'LicencaPremio', 'LicencaPremio', 'LicencaPremio']);
    $objeto->set_metodo([null, "exibePeriodoAquisitivo2", null, 'get_numDiasFruidosPorPublicacao', 'get_numDiasDisponiveisPorPublicacao', 'exibeDoerj']);

    $objeto->set_numeroOrdem(true);
    $objeto->set_numeroOrdemTipo("d");
    $objeto->set_exibeTempoPesquisa(false);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbpublicacaopremio');

    # Nome do campo id
    $objeto->set_idCampo('idPublicacaoPremio');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Foco do form
    $objeto->set_formFocus('dtPublicacao');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'dtPublicacao',
            'label' => 'Data da Pub. no DOERJ:',
            'autofocus' => true,
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'required' => true,
            'title' => 'Data da Publicação no DOERJ.',
            'linha' => 1),
        array('nome' => 'dtInicioPeriodo',
            'label' => 'Período Aquisitivo Início:',
            'tipo' => 'data',
            'col' => 3,
            'size' => 20,
            'required' => true,
            'title' => 'Data de início do período aquisitivo',
            'linha' => 1),
        array('nome' => 'dtFimPeriodo',
            'label' => 'Período Aquisitivo Término:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'required' => true,
            'title' => 'Data de término do período aquisitivo',
            'linha' => 1),
        array('nome' => 'numDias',
            'label' => 'Dias:',
            'tipo' => 'numero',
            'padrao' => 90,
            'readOnly' => true,
            'size' => 5,
            'col' => 2,
            'required' => true,
            'title' => 'Dias de Férias.',
            'linha' => 1),
        array('linha' => 5,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'linha' => 3,
            'col' => 12,
            'size' => array(80, 5)),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 6)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);
    
    # Dados da rotina de Upload
    $pasta = PASTA_PUBLICACAO_PREMIO;
    $nome = "Publicação";
    $tabela = "tbpublicacaopremio";
    $extensoes = ["pdf"];

    # Botão de Upload
    if (!empty($id)) {

        # Botão de Upload
        $botao = new Button("Upload {$nome}");
        $botao->set_url("?fase=upload&id={$id}");
        $botao->set_title("Faz o Upload do {$nome}");
        $botao->set_target("_blank");

        $objeto->set_botaoEditarExtra([$botao]);
    }

    ################################################################
    switch ($fase) {
        case "" :
        case "listar" :
            # Exibe quadro de licença prêmio
            #Grh::quadroLicencaPremio($idServidorPesquisado);
            # Pega os dados para o alerta
            $licenca = new LicencaPremio();
            $diasPublicados = $licenca->get_numDiasPublicados($idServidorPesquisado);
            $diasFruidos = $licenca->get_numDiasFruidos($idServidorPesquisado);
            $diasDisponiveis = $licenca->get_numDiasDisponiveis($idServidorPesquisado);

            # Exibe alerta se $diasDisponíveis for negativo
            if ($diasDisponiveis < 0) {
                $mensagem1 = "Servidor tem mais dias fruídos de Licença prêmio do que publicados.";
                $objeto->set_rotinaExtraListar("callout");
                $objeto->set_rotinaExtraListarParametro($mensagem1);
                #$objeto->set_botaoIncluir(false);
            }

            if ($diasDisponiveis == 0) {
                $mensagem1 = "Servidor sem dias disponíveis. É necessário cadastrar uma publicação antes de incluir uma licença prêmio.";
                $objeto->set_rotinaExtraListar("callout");
                $objeto->set_rotinaExtraListarParametro($mensagem1);
                #$objeto->set_botaoIncluir(false);
            }


            $objeto->listar();
            break;

        case "editar" :
        case "gravar" :
        case "excluir" :
            $objeto->$fase($id);
            break;
        
    ################################################################

        case "upload" :
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Exibe o Título
            if (!file_exists("{$pasta}{$id}.pdf")) {
                br();

                # Título
                tituloTable("Upload do {$nome}");

                # do Log
                $atividade = "Fez o upload do {$nome}";
            } else {
                # Monta o Menu
                $menu = new MenuBar();

                $botaoApaga = new Button("Excluir o Arquivo");
                $botaoApaga->set_url("?fase=apagaDocumento&id={$id}");
                $botaoApaga->set_title("Exclui o Arquivo PDF cadastrado");
                $botaoApaga->set_class("button alert");
                $botaoApaga->set_confirma("Tem certeza que você deseja excluir o arquivo do {$nome}?");
                $menu->add_link($botaoApaga, "right");
                $menu->show();

                # Título
                tituloTable("Substituir o Arquivo Cadastrado");

                # Define o link de voltar após o salvar
                $voltarsalvar = "?fase=uploadTerminado";

                # do Log
                $atividade = "Substituiu o arquivo do {$nome}";
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
            if (file_exists("{$pasta}{$id}.pdf")) {
                p("Já existe um documento para este registro!!<br/>O novo documento irá substituir o antigo !", "puploadMensagem");
                br();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "uploadTerminado" :
            # Informa que o bim foi substituído
            alert("Arquivo do {$nome} Cadastrado !!");

            # Fecha a janela
            echo '<script type="text/javascript" language="javascript">window.close();</script>';
            break;

        case "apagaDocumento" :

            # Apaga o arquivo (na verdade renomeia)
            if (rename("{$pasta}{$id}.pdf", "{$pasta}apagado_{$id}_" . $intra->get_usuario($idUsuario) . "_" . date("Y.m.d_H:i") . ".pdf")) {
                alert("Arquivo Excluído !!");

                # Registra log
                $atividade = "Excluiu o arquivo do {$nome}";
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

        ################################################################    
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}