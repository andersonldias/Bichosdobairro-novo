-- Adiciona campos detalhados de endereço na tabela clientes
ALTER TABLE clientes
  ADD COLUMN cep VARCHAR(9) AFTER endereco,
  ADD COLUMN logradouro VARCHAR(100) AFTER cep,
  ADD COLUMN numero VARCHAR(10) AFTER logradouro,
  ADD COLUMN complemento VARCHAR(50) AFTER numero,
  ADD COLUMN bairro VARCHAR(50) AFTER complemento,
  ADD COLUMN cidade VARCHAR(50) AFTER bairro,
  ADD COLUMN estado VARCHAR(2) AFTER cidade; 