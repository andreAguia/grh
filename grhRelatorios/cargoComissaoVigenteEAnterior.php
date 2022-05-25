<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = 'SELECT tbcomissao.idTipoComissao,
                      tbcomissao.idComissao,
                      tbcomissao.idComissao,
                      tbcomissao.idComissao
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                 LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)
                                      JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                                      JOIN tbdescricaocomissao  USING (idDescricaoComissao)    
           WHERE tbtipocomissao.ativo IS true
             AND (tbcomissao.dtExo IS null OR CURDATE() < tbcomissao.dtExo)                    
           ORDER BY tbtipocomissao.simbolo, tbtipocomissao.descricao, tbdescricaocomissao.descricao, tbcomissao.dtNom desc';

    $result = $pessoal->select($select);

    # Monta a tabela
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($result);
    $relatorio->set_label(['Cargo', 'Descrição', 'Atualmente Ocupado por:', 'Ateriormente Ocupado por:']);
    $relatorio->set_width([10, 30, 25, 25]);
    $relatorio->set_titulo("Relatório Servidores Comissionados Vigentes e Anteriors");
    $relatorio->set_align(["left", "left", "left", "left", "center"]);    
    $relatorio->set_classe(["CargoComissao", "CargoComissao", "CargoComissao", "CargoComissao"]);
    $relatorio->set_metodo(["exibeDadosVagas", "exibeDescricaoComissao", "exibeNomeadoVigente", "exibeOcupanteAnterior"]);
    $relatorio->set_numGrupo(0);
    $relatorio->set_bordaInterna(true);
    $relatorio->show();

    $page->terminaPagina();
}