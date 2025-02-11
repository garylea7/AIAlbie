from flask import Flask, request, jsonify, render_template
import os
from abacus_ai import AbacusAI  # We'll use Abacus.ai's Python SDK

app = Flask(__name__)

# Initialize Abacus.ai client
abacus_client = AbacusAI(api_key=os.getenv('ABACUS_API_KEY'))

class PromptOptimizer:
    def __init__(self):
        self.templates = {
            'general': "Convert this basic prompt into an optimized version. Basic prompt: {input_prompt}",
            'coding': "Enhance this coding-related prompt for better results. Basic prompt: {input_prompt}",
            'creative': "Transform this creative prompt for better artistic results. Basic prompt: {input_prompt}",
            'business': "Optimize this business-related prompt for professional results. Basic prompt: {input_prompt}"
        }
    
    def optimize_prompt(self, input_prompt, category='general'):
        template = self.templates.get(category, self.templates['general'])
        
        # Use Abacus.ai to generate optimized prompt
        response = abacus_client.generate_text(
            prompt=template.format(input_prompt=input_prompt),
            max_tokens=200,
            temperature=0.7
        )
        
        return response.text

optimizer = PromptOptimizer()

@app.route('/')
def home():
    return render_template('index.html')

@app.route('/optimize', methods=['POST'])
def optimize():
    data = request.json
    input_prompt = data.get('prompt')
    category = data.get('category', 'general')
    
    optimized = optimizer.optimize_prompt(input_prompt, category)
    
    return jsonify({
        'original': input_prompt,
        'optimized': optimized,
        'category': category
    })

if __name__ == '__main__':
    app.run(debug=True)
