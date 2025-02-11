class PromptTemplates:
    @staticmethod
    def get_template(category):
        templates = {
            'general': {
                'format': """
                Optimize this prompt for better AI responses:
                {input_prompt}
                
                Consider:
                - Clarity and specificity
                - Context and background
                - Desired output format
                - Constraints and requirements
                """,
                'examples': [
                    "Write a blog post about AI",
                    "Explain quantum computing",
                ]
            },
            'coding': {
                'format': """
                Enhance this coding-related prompt:
                {input_prompt}
                
                Include:
                - Language/framework specifications
                - Input/output examples
                - Performance requirements
                - Error handling expectations
                """,
                'examples': [
                    "Write a function to sort an array",
                    "Create an API endpoint",
                ]
            },
            'creative': {
                'format': """
                Transform this creative prompt:
                {input_prompt}
                
                Add:
                - Style references
                - Mood and atmosphere
                - Technical specifications
                - Inspiration sources
                """,
                'examples': [
                    "Design a logo for a tech company",
                    "Write a story about the future",
                ]
            },
            'business': {
                'format': """
                Optimize this business prompt:
                {input_prompt}
                
                Include:
                - Industry context
                - Target audience
                - Success metrics
                - Compliance requirements
                """,
                'examples': [
                    "Create a marketing strategy",
                    "Write a business proposal",
                ]
            }
        }
        return templates.get(category, templates['general'])
