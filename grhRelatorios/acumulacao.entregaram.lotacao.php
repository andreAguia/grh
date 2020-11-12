<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

# Pega os parâmetros
$parametroAno = get_session('parametroAno', date("Y"));
$parametroLotacao = get_session('parametroLotacao', '*');

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######  
    # Pega os dados
    $select = "SELECT tbservidor.idFuncional,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao,
                      dtEntrega,
                      CONCAT('SEI-',processo)
                 FROM tbacumulacaodeclaracao LEFT JOIN tbservidor USING (idServidor)
                                             LEFT JOIN tbpessoa USING (idPessoa)
                                             LEFT JOIN tbhistlot USING (idServidor)
                                             LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND anoReferencia = '{$parametroAno}'";

    # lotacao
    if ($parametroLotacao <> "*") {
        # Verifica se o que veio é numérico
        if (is_numeric($parametroLotacao)) {
            $select .= " AND (tblotacao.idlotacao = '{$parametroLotacao}')";
        } else { # senão é uma diretoria genérica
            $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
        }
    }

    $select .= " ORDER BY lotacao, tbpessoa.nome";

    $resumo = $pessoal->select($select);

    # Monta o Relatório
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($resumo);
    $relatorio->set_label(["IdFuncional", "Servidor", "Cargo", "Lotação", "Entregue em", "Processo"]);
    $relatorio->set_align(["center", "left", "left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, "date_to_php"]);

    $relatorio->set_metodo([null, "get_nome", "get_cargo"]);
    $relatorio->set_classe([null, "Pessoal", "Pessoal"]);

    $relatorio->set_titulo("Servidores Ativos que Entregaram a Declaração Anual de Acumulação");
    $relatorio->set_tituloLinha2($parametroAno);
    $relatorio->set_subtitulo("Ordenado pelo Nome do Servidor");
    $relatorio->set_numGrupo(3);
    $relatorio->show();

    $page->terminaPagina();
}