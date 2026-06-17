import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/product.dart';
import '../services/product_service.dart';
import '../providers/cart_provider.dart';
import 'cart_screen.dart'; // N'oublie pas d'importer ton écran CartScreen

class ProductListScreen extends StatefulWidget {
  const ProductListScreen({super.key});

  @override
  State<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  final ProductService _productService = ProductService();
  List<Product> _allProducts = [];
  List<Product> _filteredProducts = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadProducts();
  }

  Future<void> _loadProducts() async {
    try {
      final products = await _productService.getProducts();
      setState(() {
        _allProducts = products;
        _filteredProducts = products;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  void _runFilter(String enteredKeyword) {
    List<Product> results = _allProducts.where((p) => 
      p.name.toLowerCase().contains(enteredKeyword.toLowerCase()) || 
      p.reference.toLowerCase().contains(enteredKeyword.toLowerCase())
    ).toList();
    setState(() => _filteredProducts = results);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Stock Produits"),
        actions: [
          IconButton(
            icon: const Icon(Icons.shopping_cart),
            onPressed: () {
              // Navigation vers CartScreen
              Navigator.push(
                context, 
                MaterialPageRoute(builder: (context) => const CartScreen())
              );
            },
          )
        ],
      ),
      body: _isLoading 
        ? const Center(child: CircularProgressIndicator())
        : Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(12.0),
                child: TextField(
                  onChanged: _runFilter,
                  decoration: const InputDecoration(labelText: 'Rechercher...', prefixIcon: Icon(Icons.search)),
                ),
              ),
              Expanded(
                child: ListView.builder(
                  itemCount: _filteredProducts.length,
                  itemBuilder: (context, index) {
                    final product = _filteredProducts[index];
                    return Card(
                      color: product.isCritical ? Colors.red[50] : Colors.white,
                      child: ListTile(
                        title: Text(product.name, style: TextStyle(color: product.isCritical ? Colors.red : Colors.black)),
                        subtitle: Text("Stock: ${product.quantite} | Prix: ${product.prix} FCFA"),
                        trailing: IconButton(
                          icon: const Icon(Icons.add_shopping_cart, color: Colors.indigo),
                          onPressed: () {
                            Provider.of<CartProvider>(context, listen: false).addToCart(product);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(content: Text("${product.name} ajouté au panier !"), duration: const Duration(milliseconds: 500)),
                            );
                          },
                        ),
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
    );
  }
}