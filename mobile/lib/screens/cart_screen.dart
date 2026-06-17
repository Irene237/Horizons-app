import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/cart_provider.dart';

class CartScreen extends StatelessWidget {
  const CartScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final cart = Provider.of<CartProvider>(context);

    return Scaffold(
      appBar: AppBar(title: const Text("Votre Panier")),
      body: cart.items.isEmpty
          ? const Center(child: Text("Le panier est vide"))
          : Column(
              children: [
                Expanded(
                  child: ListView.builder(
                    itemCount: cart.items.length,
                    itemBuilder: (context, index) {
                      final item = cart.items[index];
                      return ListTile(
                        title: Text(item.product.name),
                        subtitle: Text("${item.quantity} x ${item.product.prix} FCFA"),
                        trailing: IconButton(
                          icon: const Icon(Icons.delete, color: Colors.red),
                          onPressed: () => cart.removeItem(item.product.id),
                        ),
                      );
                    },
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(20.0),
                  child: Column(
                    children: [
                      Text("Total : ${cart.totalAmount.toStringAsFixed(2)} FCFA",
                          style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 20),
                      SizedBox(
                        width: double.infinity,
                        height: 50,
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.indigo, foregroundColor: Colors.white),
                          onPressed: () {
                            // Ici, tu ajouteras plus tard l'appel API pour valider la vente
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text("Vente validée !")),
                            );
                            cart.clear();
                            Navigator.pop(context);
                          },
                          child: const Text("VALIDER LA VENTE"),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }
}