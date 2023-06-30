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

    $select = 'SELECT tbservidor.idServidor,
                    tbpessoa.nome,
                    tbcomissao.dtNom,
                    tbcomissao.dtPublicNom,
                    tbcomissao.idComissao,
                    idPerfil,
                    concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao)
               FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                               LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)
                                    JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                                    JOIN tbdescricaocomissao  USING (idDescricaoComissao)
                                    JOIN tbtiponomeacao ON (tbcomissao.tipo = tbtiponomeacao.idTipoNomeacao)
           WHERE tbtiponomeacao.visibilidade <> 2
             AND tbtipocomissao.ativo IS true
             AND (tbcomissao.dtExo IS null OR CURDATE() < tbcomissao.dtExo) 
             AND tbcomissao.tipo <> 3
           ORDER BY tbtipocomissao.simbolo, tbdescricaocomissao.descricao, tbcomissao.dtNom desc';

    $result = $pessoal->select($select);

    $label = ['Id / Matrícula', 'Nome', 'Nomeação', 'Publicação', 'Nome do Cargo', 'Perfil', 'Cargo'];
    $align = ["center", "left", "center", "center", "left", "center"];
    $function = ["idMatricula", null, "date_to_php", "date_to_php"];
    $classe = [null, null, null, null, "CargoComissao", "Pessoal"];
    $metodo = [null, null, null, null, "get_descricaoCargo", "get_perfil"];

    # Monta a tabela
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($result);
    $relatorio->set_label($label);
    $relatorio->set_titulo("Relatório Servidores Comissionados");
    $relatorio->set_align($align);
    $relatorio->set_funcao($function);
    $relatorio->set_classe($classe);
    $relatorio->set_metodo($metodo);
    $relatorio->set_numGrupo(6);
    $relatorio->show();

    $page->terminaPagina();
}