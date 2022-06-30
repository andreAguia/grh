<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $reducao = new ReducaoCargaHoraria();

    # Pega o id
    $id = get('id');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # pega os dados
    $dados = $reducao->get_dadosReducao($id);
    $dtAtoReitor = date_to_php($dados["dtAtoReitor"]);
    $dtDespacho = date_to_php($dados["dtDespacho"]);
    $periodo = $dados["periodo"];
    $necessidade = get('necessidade');

    # do Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado, false);
    $lotacao = $pessoal->get_lotacao($idServidorPesquisado);
    $sexo = $pessoal->get_sexo($idServidorPesquisado);

    # Altera parte do texto de acordo com o sexo (gênero) do servidor
    if ($sexo == "Masculino") {
        $texto1 = "do servidor";
        $texto2 = "lotado";
    } else {
        $texto1 = "da servidora";
        $texto2 = "lotada";
    }

    # da Redução
    $processo = $reducao->get_numProcesso($idServidorPesquisado);

    # do Ato
    $textoReitor = "O <b>REITOR DA UNIVERSIDADE ESTADUAL DO NORTE FLUMINENSE DARCY RIBEIRO – UENF</b>,"
            . " tendo em vista as suas atribuições estabelecidas no Decreto nº 30.672, de 18/02/2002 e o que consta no Processo nº $processo,";

    $textoPrincipal = "Reduz em 50% a carga horária de trabalho $texto1 <b>" . strtoupper($nomeServidor) . "</b>, {$cargoEfetivo}, ID nº {$idFuncional}, {$texto2} na {$lotacao},
                       pelo prazo de {$periodo} (" . numero_to_letra($periodo) . ") meses ou enquanto responsável legal por pessoa portadora de necessidade caracterizada como " . strtolower($necessidade) . ",
                       que requeira atenção do responsável, conforme artigo 6º do decreto nº 14.870/90, regulamentado pela Resolução SARE nº 3.004 de 20/05/2003
                       e o despacho da Coordenadoria Geral da Superintendência de Perícias Médicas e Saúde Ocupacional – SPMSO, da Secretaria de Estado de Saúde – SES,
                       datado de {$dtDespacho}, constante do presente processo.";

    # Ato do Reitor
    $ato = new AtoReitor();
    $ato->set_data($dtAtoReitor);
    $ato->set_textoReitor($textoReitor);
    $ato->set_textoPrincipal($textoPrincipal);
    $ato->set_saltoRodape(1);
    $ato->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou O Ato do Reitor de redução da carga horária: ';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", $id, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}