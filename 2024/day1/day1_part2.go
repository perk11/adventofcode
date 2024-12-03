package main

import (
	"fmt"
	"os"
	"slices"
	"strconv"
	"strings"
)

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		list1 := make([]int, len(lines))
		list2 := make([]int, len(lines))
		for index, line := range lines {
			split := strings.Fields(line)
			list1[index], _ = strconv.Atoi(strings.Trim(split[0], " "))
			list2[index], _ = strconv.Atoi(strings.Trim(split[1], " "))
		}

		var similarityScore int = 0
		for _, element := range list1 {
			similarityScore += element * findNumberOfOccurrencesInSlice(list2, element, 0)
		}
		println(similarityScore)
	}
}
func findNumberOfOccurrencesInSlice(slice []int, searchable int, priorOccurrences int) int {
	var index = slices.Index(slice, searchable)
	if index == -1 {
		return priorOccurrences
	}

	return findNumberOfOccurrencesInSlice(slice[index+1:], searchable, priorOccurrences+1)
}
