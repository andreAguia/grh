<?php

/**
 * Histórico de Formação Escolar do Servidor
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Formação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica de onde veio
    $origem = get_session("origem");

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

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
    $objeto->set_nome('Cadastro da Formação Escolar do Servidor');

    # botão de voltar da lista
    if ($origem == "areaFormacao") {
        $objeto->set_voltarLista('areaFormacao.php');
    } else {
        $objeto->set_voltarLista('servidorMenu.php');
    }

    # select da lista
    $objeto->set_selectLista('SELECT escolaridade,
                                     habilitacao,
                                     instEnsino,
                                     anoTerm,                              
                                     idFormacao,
                                     idFormacao,
                                     idFormacao
                                FROM tbformacao JOIN tbescolaridade USING (idEscolaridade)
                          WHERE idPessoa=' . $idPessoa . '
                       ORDER BY anoTerm desc');

    # select do edita
    $objeto->set_selectEdita('SELECT idEscolaridade,
                                     habilitacao,
                                     instEnsino,                                     
                                     horas,
                                     anoTerm,
                                     obs,
                                     idPessoa
                                FROM tbformacao
                               WHERE idFormacao = ' . $id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Nível", "Curso", "Instituição", "Ano de Término", "Ver", "Upload"]);
    $objeto->set_width([10, 30, 30, 10, 5, 5]);
    $objeto->set_align(["center", "left", "left"]);

    $objeto->set_classe([null, null, null, null, "Formacao", "Formacao"]);
    $objeto->set_metodo([null, null, null, null, "exibeCertificado", "exibeBotaoUpload"]);

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbformacao');

    # Nome do campo id
    $objeto->set_idCampo('idFormacao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da combo escolaridade
    $result = $pessoal->select('SELECT idEscolaridade, 
                                            escolaridade
                                       FROM tbescolaridade
                                   ORDER BY idEscolaridade');
    array_unshift($result, array(null, null));

    # Pega os dados da datalist curso
    $cursos = $pessoal->select('SELECT distinct habilitacao
                                       FROM tbformacao
                                   ORDER BY habilitacao');
    array_unshift($cursos, array(null));

    # Pega os dados da datalist instEnsino
    $instEnsino = $pessoal->select('SELECT distinct instEnsino
                                       FROM tbformacao
                                   ORDER BY instEnsino');
    array_unshift($instEnsino, array(null));

    # Campos para o formulario
    $objeto->set_campos(array(array('nome' => 'idEscolaridade',
            'label' => 'Nível:',
            'tipo' => 'combo',
            'array' => $result,
            'required' => true,
            'autofocus' => true,
            'size' => 20,
            'col' => 4,
            'title' => 'Nível do Curso.',
            'linha' => 1),
        array('nome' => 'habilitacao',
            'label' => 'Curso:',
            'tipo' => 'texto',
            'datalist' => $cursos,
            'plm' => true,
            'size' => 80,
            'col' => 8,
            'required' => true,
            'title' => 'Nome do curso.',
            'linha' => 1),
        array('nome' => 'instEnsino',
            'label' => 'Instituição de Ensino:',
            'tipo' => 'texto',
            'datalist' => $instEnsino,
            'size' => 80,
            'plm' => true,
            'col' => 7,
            'required' => true,
            'title' => 'Nome da Instituição de Ensino.',
            'linha' => 2),
        array('nome' => 'horas',
            'label' => 'Carga Horária:',
            'tipo' => 'numero',
            'size' => 10,
            'col' => 2,
            'title' => 'Carga Horária do Curso.',
            'linha' => 2),
        array('nome' => 'anoTerm',
            'label' => 'Ano de Término:',
            'tipo' => 'numero',
            'size' => 5,
            'col' => 3,
            'title' => 'Nome da Instituição de Ensino.',
            'linha' => 2),
        array('linha' => 3,
            'nome' => 'obs',
            'col' => 12,
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5)),
        array('nome' => 'idPessoa',
            'label' => 'idPessoa:',
            'tipo' => 'hidden',
            'padrao' => $idPessoa,
            'size' => 6,
            'title' => 'idPessoa',
            'linha' => 5)));
    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Formação");
    $botaoRel->set_onClick("window.open('../grhRelatorios/servidorFormacao.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
    $objeto->set_botaoListarExtra(array($botaoRel));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
        case "editar" :
        case "excluir" :
        case "gravar" :
            $objeto->$fase($id);
            break;
        ################################################################

        case "upload" :
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Botão voltar
            botaoVoltar('?');

            # Título
            tituloTable("Upload de Certificado / Diploma de Curso");

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
            $pasta = PASTA_CERTIFICADO;

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
                    $atividade = "Fez o upload de publicação de concurso";
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

        ##################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}